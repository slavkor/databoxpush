<?php
namespace App\Factory;


use Google\Client as Google;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_Analytics;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Domain\Model\Metric;
use App\Domain\Model\DataboxPushData;
use App\Domain\Factory\IMetricsClient;
use App\Domain\Model\WatchedObject;


/**
 * Description of GoogleMetricsClient
 *
 * @author Slavko
 */
final class GoogleMetricsClient implements IMetricsClient {

    /**
     *
     * @var array
     */
    private $settings;

    /**
     *
     * @var Session 
     */
    private $session;
    
    /**
     *
     * @var Google
     */
    private $google;
    
    public function __construct(Session $session, $settings) {
        $this->session = $session; 
        $this->settings = $settings;
        $this->google = new Google();
    }
    
    public function GetMetricsFroWatchedObject(WatchedObject $object): DataboxPushData {
        $this->google->setAccessToken($this->session->get('access_token'));
        $analytics = new Google_Service_AnalyticsReporting($this->google);
  
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

    public function GetWathedObject(): WatchedObject {
        $this->google->setAccessToken($this->session->get('access_token'));
        // Create an authorized analytics service object.
        $analytics = new Google_Service_Analytics($this->google);
        $profile = $this->getFirstProfileId($analytics);
        
        $watched = new WatchedObject();
        $watched->setObjectId($profile);
        $watched->setObjectProperties(['ga:sessions', 'ga:users','ga:entrances', 'ga:pageviews', 'ga:bounces']);
        return $watched;

    }

    public function SetSession(Session $session) {
        $this->session = $session;
    }

    private function getFirstProfileId($analytics){
        // Get the user's first view (profile) ID.

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
            throw new Exception('No views (profiles) found for this user.');
          }
        } else {
          throw new Exception('No properties found for this user.');
        }
      } else {
        throw new Exception('No accounts found for this user.');
      }

    }
}

