<?php
namespace App\Domain\Push\Repository;
use App\Repository\RepositoryInterface;
use App\Domain\User\Data\UserAuthData;
use App\Domain\Push\Data\WatchedObject;
use App\Domain\Push\Data\DataboxPushData;
use App\Domain\Push\Data\Metric;
use Google\Client as Google;
use Google_Service_Analytics;
use UnexpectedValueException;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Databox\Client as DataboxClient;
use Cake\Chronos\Chronos;

use App\Factory\QueryFactory;
use App\Repository\DataTableRepository;
use App\Repository\TableName;

/**
 * 
 * Handels the communication with external services and database. 
 * 
 * 
 */
class PushProviderRepository implements RepositoryInterface{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var DataTableRepository
     */
    private $dataTable;
    
    public function __construct(QueryFactory $queryFactory, DataTableRepository $dataTableRepository) {
        $this->queryFactory = $queryFactory;
        $this->dataTable = $dataTableRepository;
    }
    
    /**
     * Gets the first object for the $user  that it can find to observe the metrics. This method returns the object for wich the metrics will be pushed to databox service
     * @param UserAuthData $user
     * @return WatchedObject
     */
    public function GetObject(UserAuthData $user) : WatchedObject{
        
        switch ($user->origin) {
            case 'google':

                $client = new Google();
                $client->setAccessToken($user->access_token);
                // Create an authorized analytics service object.
                $analytics = new Google_Service_Analytics($client);
                $profile = $this->getFirstProfileId($analytics);

                $watched = new WatchedObject();
                $watched->setObjectId($profile);
                $watched->setObjectProperties(['ga:sessions', 'ga:users','ga:entrances', 'ga:pageviews', 'ga:bounces']);
                return $watched;
            default:
                break;
        }
    } 
    
    /**
     * 
     *  Get's the data for a specific object
     * 
     * @param UserAuthData $user
     * @param WatchedObject $object
     * @return DataboxPushData
     */
    public function GetObjectMetrics(UserAuthData $user, WatchedObject $object) : DataboxPushData{
        $client = new Google();
        $client->setAccessToken($user->access_token);
        $analytics = new Google_Service_AnalyticsReporting($client);
  
        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");

        foreach ($object->getObjectProperties() as $key) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($key);
            $metric->setAlias($key);
            $metrics[] = $metric;
        }
        
        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($object->getObjectId());
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        $reports = $analytics->reports->batchGet( $body );  
   
        //prepare the responses for DataboxPush middleware
        for ($reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++){
            $report = $reports[ $reportIndex ];
            $header = $report->getColumnHeader();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++){
                $row = $rows[ $rowIndex ];
                $metrics = $row->getMetrics();
                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        $mtrcs[] = new Metric($entry->getName(),$values[$k] );
                    }
                }
            }
        }
        // return to DataboxPush middleware
        return new DataboxPushData('', $mtrcs);
    }

    /**
     * Pushes the metrics data  to databox service 
     * @param UserAuthData $user
     * @param DataboxPushData $data
     * @return type
     */
    public function ExecutePush(UserAuthData $user, DataboxPushData $data){
                //push to client
        $client = new DataboxClient($data->getPushkey());

        $metrics ="";
        $values = "";
        
        foreach ($data->getMetrics() as $metric) {
            $kpis[] = [$metric->getKey(), $metric->getValue()];
            $metrics .= $metric->getKey() . ",";
            $values .= $metric->getValue(). ",";
        }
        
        $response = $client->insertAll($kpis);
        
        $last = $client->getPush($response);

        $push = [
            'origin' => $user->origin,
            'metrics' => $metrics,
            'values' => $values
        ];
        return $this->InsertPush($user, $data, $push);
    }
    
    /**
     * Insert's the metrics data to a local database
     * @param UserAuthData $user
     * @param DataboxPushData $data
     * @param array $row
     * @return int
     */
    public function InsertPush(UserAuthData $user, DataboxPushData $data, array $row) : int{
         $row['date'] = Chronos::now()->toDateTimeString();
         return (int)$this->queryFactory->newInsert(TableName::PUSH, $row)->execute()->lastInsertId();
    }
    
    /**
     * Load data table entries.
     *
     * @param array $params The push
     *
     * @return array The table data
     */
    public function getTableData(array $params): array
    {
        $query = $this->queryFactory->newSelect('push');
        $query->select(['push.*']);

        return $this->dataTable->load($query, $params);
    }    
    
    private function getFirstProfileId($analytics){
        // Get the list of accounts for the authorized user.
        $accounts = $analytics->management_accounts->listManagementAccounts();
        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                ->listManagementWebproperties($firstAccountId);
            if (count($properties->getItems()) > 0) {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();
                // Get the list of views (profiles) for the authorized user.
                $profiles = $analytics->management_profiles
                    ->listManagementProfiles($firstAccountId, $firstPropertyId);
                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();
                    // Return the first view (profile) ID.
                    return $items[0]->getId();

                } else {
                    throw new UnexpectedValueException('No views (profiles) found for this user.');
                }
            } else {
                throw new UnexpectedValueException('No properties found for this user.');
            }
        } else {
             throw new UnexpectedValueException('No accounts found for this user.');
        }
    }
}
