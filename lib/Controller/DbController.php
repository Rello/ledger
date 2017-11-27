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
use phpDocumentor\Reflection\Types\Integer;

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
     * Get the totals for a group
     *
     * @param string $group_id
     * @return array
     */
    public function getTotalsforGroup($group_id){
		$totals=array();
        //Einnahmen
		$SQL="SELECT SUM(`value`) AS `value`
						FROM `*PREFIX*ledger_booking`
			 			WHERE  `group_id` = ?
			 			AND (`valuetype` = '1'
			 			OR `valuetype` = '3')
			 			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetch();
        $totals['kpi2'] = 0 + $results['value'];

        //Ausgaben
        $SQL="SELECT SUM(`value`) AS `value`
						FROM `*PREFIX*ledger_booking`
			 			WHERE  `group_id` = ?
			 			AND `valuetype` = '2'
			 			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id));
        $results = $stmt->fetch();
        $totals['kpi3'] = 0 + $results['value'];

        $totals['kpi1']=$totals['kpi2'] - $totals['kpi3'];
        return $totals;
    }

    /**
     * Get the members of a group
     *
     * @param string $group_id
     * @return array
     */
    public function getMembersOfGroup($group_id){
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
     * Get the timeline items for a group
     *
     * @param string $group_id
     * @return array
     */
    public function getTimelineItems($group_id){
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
     * Get the timeline columns
     *
     * @param string $group_id
     * @param string $member_id
     * @return array
     */
    public function getTimelineColumns($group_id, $member_id){
        $SQL="SELECT `id`, `month`, `version`
            FROM `oc_ledger_booking`
            WHERE  `group_id` = ?
            AND `member_id` = ?
            AND `valuetype` = 3
            ORDER BY `month` ASC
			";
        $stmt = $this->db->prepare($SQL);
        $stmt->execute(array($group_id, $member_id));
        $results = $stmt->fetchAll();
        return $results;
    }

    /**
     * Get the transactions for a group
     *
     * @param string $group_id
     * @return array
     */
    public function getTransactionItems($group_id){
        $timeline_row = array();
        $SQL="SELECT `date`, `V`.`name` AS `type`, `M`.`name` AS `member`,`value`, `month`, `note`
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
    public function addBooking($booking){
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
     * Get group id by member id
     *
     * @param integer $member_id
     * @return integer
     */
    public function getGroupByMember($member_id){
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
    public function getGroupDefault($group_id){
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
     * add a member entry
     *
     * @param array $item
     * @return integer
     */
    public function addMember($item){
        $stmt = $this->db->prepare('INSERT INTO `*PREFIX*ledger_member` (`user_id`,`name`,`group_id`) VALUES(?,?,?)');
        $stmt->execute(array(
            $item['user_id'],
            $item['name'],
            $item['group_id'],
        ));
        $insertid = $this->db->lastInsertId('*PREFIX*ledger_member');
        return $insertid;
    }

    /**
     * edit a member entry
     *
     * @param Integer $id
     * @param Integer $user_id
     * @param Integer $name
     * @return integer
     */
    public function editMember($id, $user_id, $name){
        $stmt = $this->db->prepare('UPDATE `*PREFIX*ledger_member` SET `user_id` = ? ,`name` = ? WHERE `id` = ?');
        $stmt->execute(array(
            $user_id,
            $name,
            $id,
        ));
        return true;
    }

    /**
     * delete a member entry
     *
     * @param Integer $id
     * @return boolean
     */
    public function deleteMember($id){
        $stmt = $this->db->prepare('delete from `*PREFIX*ledger_member` WHERE `id` = ?');
        $stmt->execute(array(
            $id,
        ));
        return true;
    }

    /**
     * edit a timeline entry
     *
     * @param Integer $id
     * @param Integer $month
     * @param Integer $version
     * @return boolean
     */
    public function editTimeline($id, $month, $version){
        $stmt = $this->db->prepare('UPDATE `*PREFIX*ledger_booking` SET `month` = ? ,`version` = ? WHERE `id` = ?');
        $stmt->execute(array(
            $month,
            $version,
            $id,
        ));
        return true;
    }

    /**
     * delete a timeline entry
     *
     * @param Integer $id
     * @return boolean
     */
    public function deleteTimeline($id){
        $stmt = $this->db->prepare('delete from `*PREFIX*ledger_booking` WHERE `id` = ?');
        $stmt->execute(array(
            $id,
        ));
        return true;
    }

}

