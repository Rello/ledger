<?php
/**
 * Audio Player
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <audioplayer@scherello.de>
 * @copyright 2016-2017 Marcel Scherello
 */

namespace OCA\Ledger\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDbConnection;

/**
 * Controller class for main page.
 */
class DbController extends Controller {
	
	private $userId;
	private $db;

	public function __construct(
        $AppName,
        IRequest $request,
        $UserId,
        IDBConnection $db
    ) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->db = $db;
		}

	/**
	 * @NoAdminRequired
	 * 
	 */
	public function getTotals($group_id){
		$totals= $this->getTotalsforGroup($group_id);
	
		if($totals){
			$result=[
				'status' => 'success',
				'data' => $totals
			];
		}else{
			$result=[
				'status' => 'nodata'
			];
		}
		$response = new JSONResponse();
		$response -> setData($result);
		return $response;
	}

    /**
     * Get the categories items for a user
     *
     * @param string $group_id
     * @return array
     */
	private function getTotalsforGroup($group_id){
		$totals=array();
        $SQL="SELECT SUM(`value`) AS `value`
						FROM `*PREFIX*ledger_booking`
			 			WHERE  `group_id` = ?
			 			AND `valuetype` = '1'
			 			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetch();
        $totals['kpi2'] = $results['value'];

        $SQL="SELECT SUM(`value`) AS `value`
						FROM `*PREFIX*ledger_booking`
			 			WHERE  `group_id` = ?
			 			AND `valuetype` = '2'
			 			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetch();
        $totals['kpi3'] = $results['value'];

        $totals['kpi1']=$totals['kpi2'] - $totals['kpi3'];
        return $totals;
    }

    /**
     * @NoAdminRequired
     *
     */
    public function getTimeline($group_id){
        $totals= $this->getTimelineItems($group_id);
        $members = $this->getMembersOfGroup($group_id);

        if(is_array($members)){
            $result=[
                'status' => 'success',
                'data' => ['members'=>$members,'timeline'=>$totals]
            ];
        }else{
            $result=[
                'status' => 'nodata'
            ];
        }
        $response = new JSONResponse();
        $response -> setData($result);
        return $response;
    }

    /**
     * Get the categories items for a user
     *
     * @param string $group_id
     * @return array
     */
    private function getMembersOfGroup($group_id){
        $timeline_row = array();
        $SQL="SELECT *
                FROM `*PREFIX*ledger_member`
                WHERE  `group_id` = ?
			 	";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetchAll();
        return $results;
    }

    /**
     * Get the categories items for a user
     *
     * @param string $group_id
     * @return array
     */
    private function getTimelineItems($group_id){
        $timeline_row = array();
        $SQL="SELECT `id`, `name`
                FROM `*PREFIX*ledger_member`
                WHERE  `group_id` = ?
			 	";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetchAll();
        foreach($results as $row) {
            $timeline_row[$row['id']] = $this->getTimelineColumns($group_id, $row['id']);
        }

        return $timeline_row;
    }

    /**
     * Get the categories items for a user
     *
     * @param string $group_id
     * @return array
     */
    private function getTimelineColumns($group_id, $member_id){
        $SQL="SELECT `month`, `version`
            FROM `oc_ledger_booking`
            WHERE  `group_id` = ?
            AND `member_id` = ?
            AND `valuetype` = 3
			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id, $member_id));
        $results = $stmt->fetchAll();
        return $results;
    }

    /**
     * @NoAdminRequired
     *
     */
    public function getTransactions($group_id){
        $totals= $this->getTransactionItems($group_id);

        if($totals){
            $result=[
                'status' => 'success',
                'data' => $totals
            ];
        }else{
            $result=[
                'status' => 'nodata'
            ];
        }
        $response = new JSONResponse();
        $response -> setData($result);
        return $response;
    }

    /**
     * Get the categories items for a user
     *
     * @param string $group_id
     * @return array
     */
    private function getTransactionItems($group_id){
        $timeline_row = array();
        $SQL="SELECT `date`, `V`.`name` AS `type`, `M`.`name` AS `member`,`value`, `note`
            FROM `*PREFIX*ledger_booking` `B`
            LEFT JOIN `*PREFIX*ledger_valuetype` `V`
            ON `B`.`valuetype` = `V`.`id`
            LEFT JOIN `*PREFIX*ledger_member` `M`
            ON `B`.`member_id` = `M`.`id`
            WHERE  `B`.`group_id` = ?
            AND `version` = 1
            ORDER BY `date` DESC
            LIMIT 15
  		 	";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * write a boking entry
     *
     * @param array $booking
     * @return integer
     */
    private function addBookingDB($booking){
        $stmt = $this->db->prepare('INSERT INTO `*PREFIX*ledger_booking` (`member_id`,`group_id`,`valuetype`,`value`,`month`,`date`,`version`,`note`) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute(array(
            $booking['member_id'],
            $booking['group_id'],
            $booking['valuetype'],
            $booking['value'],
            $booking['month'],
            $booking['date'],
            $booking['version'],
            $booking['note'],
        ));
        $insertid = $this->db->lastInsertId('*PREFIX*ledger_booking');
        return $insertid;
    }

    /**
     * @NoAdminRequired
     *
     */
    public function addTimeline($member_id, $month){

        $group_id = $this->getGroupByMember($member_id);
        $default = $this->getGroupDefault($group_id);

        $booking['member_id'] = $member_id;
        $booking['group_id'] = $group_id;
        $booking['valuetype'] = 3;
        $booking['value'] = $default;
        $booking['month'] = $month +1;
        $booking['date'] = '';
        $booking['version'] = 1;
        $booking['note'] ='';
        $totals = $this->addBookingDB($booking);

        if($totals){
            $result=[
                'status' => 'success',
                'data' => $totals
            ];
        }else{
            $result=[
                'status' => 'nodata'
            ];
        }
        $response = new JSONResponse();
        $response -> setData($result);
        return $response;
    }

    /**
     * Get group id by member id
     *
     * @param integer $member_id
     * @return integer
     */
    private function getGroupByMember($member_id){
        $SQL="SELECT `group_id`
			  FROM `*PREFIX*ledger_member`
			  WHERE  `id` = ?
	          ";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($member_id));
        $results = $stmt->fetch();
        return $results['group_id'];
    }

    /**
     * Get default payment of group
     *
     * @param integer $group_id
     * @return integer
     */
    private function getGroupDefault($group_id){
        $SQL="SELECT `default`
			  FROM `*PREFIX*ledger_group`
			  WHERE  `id` = ?
	          ";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetch();
        return $results['default'];
    }

    /**
     * @NoAdminRequired
     *
     */
    public function addMember($group_id){

        $member['name'] = 'Neu';
        $member['group_id'] = $group_id;

        $return = $this->addMemberDB($member);

        if($return){
            $result=[
                'status' => 'success',
                'data' => $return
            ];
        }else{
            $result=[
                'status' => 'nodata'
            ];
        }
        $response = new JSONResponse();
        $response -> setData($result);
        return $response;
    }

    /**
     * write a member entry
     *
     * @param array $member
     * @return integer
     */
    private function addMemberDB($item){
        $stmt = $this->db->prepare('INSERT INTO `*PREFIX*ledger_member` (`user_id`,`name`,`group_id`) VALUES(?,?,?)');
        $stmt->execute(array(
            $item['user_id'],
            $item['name'],
            $item['group_id'],
        ));
        $insertid = $this->db->lastInsertId('*PREFIX*ledger_member');
        return $insertid;
    }

}

