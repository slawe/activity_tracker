<?php

declare(strict_types=1);

namespace App\Activity\Infrastructure;

use App\Activity\Application\ActivitySearchQuery;
use App\Activity\Application\ActivitySearchRepositoryInterface;
use App\Activity\Application\ActivitySearchResult;
use App\Activity\Domain\ActivityAction;
use App\Activity\Domain\ActivityEvent;
use App\Activity\Domain\ActivityPage;
use App\Activity\Domain\ActivityRepositoryInterface;
use App\Activity\Domain\ActivityTarget;
use DateTimeImmutable;
use PDO;

final class PdoActivityRepository implements ActivityRepositoryInterface, ActivitySearchRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function add(ActivityEvent $event): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO activity_events
                (user_id, action, page, target, ip_address, user_agent, created_at)
             VALUES
                (:user_id, :action, :page, :target, :ip_address, :user_agent, :created_at)',
        );
        $statement->execute([
            'user_id' => $event->userId,
            'action' => $event->action->value,
            'page' => $event->page?->value,
            'target' => $event->target?->value,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'created_at' => $event->createdAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function search(ActivitySearchQuery $query): ActivitySearchResult
    {
        [$where, $parameters] = $this->filters($query);

        $countStatement = $this->connection->prepare(
            'SELECT COUNT(*) FROM activity_events ae' . $where,
        );
        $countStatement->execute($parameters);
        $total = (int) $countStatement->fetchColumn();
        $totalPages = max(1, (int) ceil($total / ActivitySearchQuery::PAGE_SIZE));
        $page = min(max(1, $query->page), $totalPages);
        $offset = ($page - 1) * ActivitySearchQuery::PAGE_SIZE;

        $sql = 'SELECT ae.user_id, ae.action, ae.page, ae.target,
                       ae.ip_address, ae.user_agent, ae.created_at, u.email AS user_email
                FROM activity_events ae
                LEFT JOIN users u ON u.id = ae.user_id'
            . $where
            . ' ORDER BY ae.created_at DESC, ae.id DESC
                LIMIT :limit OFFSET :offset';
        $statement = $this->connection->prepare($sql);

        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value);
        }
        $statement->bindValue('limit', ActivitySearchQuery::PAGE_SIZE, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $events = [];
        while ($row = $statement->fetch()) {
            $events[] = $this->hydrate($row);
        }

        return new ActivitySearchResult(
            $events,
            $total,
            $page,
            ActivitySearchQuery::PAGE_SIZE,
        );
    }

    /**
     * @return array{0: string, 1: array<string, int|string>}
     */
    private function filters(ActivitySearchQuery $query): array
    {
        $conditions = [];
        $parameters = [];

        if ($query->dateFrom !== null) {
            $conditions[] = 'ae.created_at >= :date_from';
            $parameters['date_from'] = $query->dateFrom->format('Y-m-d H:i:s');
        }
        if ($query->dateTo !== null) {
            $conditions[] = 'ae.created_at < :date_to';
            $parameters['date_to'] = $query->dateTo->format('Y-m-d H:i:s');
        }
        if ($query->userId !== null) {
            $conditions[] = 'ae.user_id = :user_id';
            $parameters['user_id'] = $query->userId;
        }
        if ($query->action !== null) {
            $conditions[] = 'ae.action = :action';
            $parameters['action'] = $query->action->value;
        }

        return [
            $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions),
            $parameters,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ActivityEvent
    {
        return new ActivityEvent(
            $row['user_id'] === null ? null : (int) $row['user_id'],
            ActivityAction::from((string) $row['action']),
            $row['page'] === null ? null : ActivityPage::from((string) $row['page']),
            $row['target'] === null ? null : ActivityTarget::from((string) $row['target']),
            (string) $row['ip_address'],
            (string) $row['user_agent'],
            new DateTimeImmutable((string) $row['created_at']),
            $row['user_email'] === null ? null : (string) $row['user_email'],
        );
    }
}
