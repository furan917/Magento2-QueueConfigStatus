<?php
declare(strict_types=1);

namespace Furan\QueueConfigStatus\Model\QueueConfig;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Topology\Config\CompositeReader;

class ChangeDetector
{
    public function __construct(
        private CompositeReader $topologyConfigReader,
        private ResourceConnection $resourceConnection
    ) {

    }

    /**
     * @return bool
     */
    public function hasChanges(): bool
    {
        $databaseQueues = $this->getQueuesFromDatabase();
        $configQueues = $this->getQueuesFromConfig();

        return $this->hasMissingQueues($databaseQueues, $configQueues);
    }

    private function getQueuesFromDatabase(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('queue');
        $select = $connection->select()->distinct()->from($tableName, ['name']);

        return $connection->fetchCol($select);
    }

    private function getQueuesFromConfig(): array
    {
        $queues = [];

        $config = $this->topologyConfigReader->read();
        foreach ($config as $exchangeName => $exchangeData) {
            if (isset($exchangeData['bindings']) && is_array($exchangeData['bindings'])) {
                foreach ($exchangeData['bindings'] as $binding) {
                    if (isset($binding['destination'], $binding['destinationType']) && $binding['destinationType'] === 'queue') {
                        $queues[] = $binding['destination'];
                    }
                }
            }
        }

        return array_unique($queues);
    }

    private function hasMissingQueues(array $databaseQueues, array $configQueues): bool
    {
        $missingQueues = array_diff($configQueues, $databaseQueues);
        return !empty($missingQueues);
    }
}
