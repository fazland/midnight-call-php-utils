<?php declare(strict_types=1);

namespace MidnightCall\Utils\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

trait DoctrineCommandTrait
{
    use BaseTrait;

    private EntityManagerInterface $entityManager;

    private Connection $connection;

    /**
     * {@inheritdoc}
     */
    private function prepareEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
        $this->connection = $this->entityManager->getConnection();
    }

    /**
     * Generates the '?' query placeholder based on the count of params.
     *
     * @param array $params
     *
     * @return string
     */
    private function getQueryPlaceholders(array $params): string
    {
        return \implode(', ', \array_map(static function (): string {
            return '?';
        }, $params));
    }

    /**
     * Fetches all results obtained by querying the select.
     *
     * @param string $select
     * @param array  $params
     *
     * @return array
     */
    private function executeSelect(string $select, array $params = []): array
    {
        try {
            return $this->connection->fetchAll($select, $params);
        } catch (DBALException $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }

    /**
     * Execute the SQL query.
     *
     * @param string $sql
     * @param array  $params
     * @param array  $types
     */
    private function executeQuery(string $sql, array $params, array $types = []): void
    {
        if (! $this->dryRun) {
            try {
                $this->connection->executeUpdate($sql, $params, $types);
            } catch (DBALException $exception) {
                $this->connection->rollBack();

                throw $exception;
            }
        }
    }

    /**
     * Retrieves target id by entity name if exists.
     *
     * @param string $tableName
     * @param string $entityName
     * @param string $fieldName
     *
     * @return string|null
     */
    private function findTargetIdByName(string $tableName, string $entityName, string $fieldName = 'name'): ?string
    {
        $select = <<<SQL
SELECT id
FROM $tableName
WHERE $fieldName = ?
SQL;

        $result = $this->executeSelect($select, [$entityName]);

        return 0 === \count($result) ? null : $result[0]['id'] ?? null;
    }

    /**
     * Commits the current transaction.
     */
    private function commit(): void
    {
        if ($this->dryRun) {
            $this->connection->rollBack();
        } else {
            $this->connection->commit();
        }
    }
}
