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
use phpDocumentor\Reflection\Types\Integer;


/**
 * Controller class for main page.
 */
class LedgerController extends Controller {
	
	private $userId;
	private $db;

	public function __construct(
        $AppName,
        IRequest $request,
        $UserId,
        DbController $db
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
		$totals= $this->db->getMembersOfGroup($group_id);
	
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
     * @NoAdminRequired
     *
     */
    public function getTimeline($group_id){
        $timeline= $this->db->getTimelineItems($group_id);
        $members = $this->db->getMembersOfGroup($group_id);

        if(is_array($members)){
            $result=[
                'status' => 'success',
                'data' => ['members'=>$members,'timeline'=>$timeline]
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
     * @NoAdminRequired
     *
     */
    public function getTransactions($group_id){
        $totals= $this->db->getTransactionItems($group_id);

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
     * @NoAdminRequired
     *
     */
    public function addTimeline($member_id, $month){

        $group_id = $this->db->getGroupByMember($member_id);
        $default = $this->db->getGroupDefault($group_id);
        if (!$month) $month = date("ym");

        $year = substr($month,0,2);
        $month = substr($month,2,2);

        if ($month > 11) {
            $month = 1;
            $year = $year + 1;
        } else {
            $month++;
        }
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);

        $booking['member_id'] = $member_id;
        $booking['group_id'] = $group_id;
        $booking['valuetype'] = 3;
        $booking['value'] = $default;
        $booking['month'] = $year.$month;
        $booking['date'] = '';
        $booking['version'] = 1;
        $booking['note'] ='';
        $totals = $this->db->addBooking($booking);

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
     * @NoAdminRequired
     *
     */
    public function addTransaction($valuetype, $value, $member_id, $note, $date){

        $group_id = $this->db->getGroupByMember($member_id);

        $booking['member_id'] = $member_id;
        $booking['group_id'] = $group_id;
        $booking['valuetype'] = $valuetype;
        $booking['value'] = $value;
        $booking['month'] = '';
        $booking['date'] = $date;
        $booking['version'] = 1;
        $booking['note'] = $note;
        $totals = $this->db->addBooking($booking);

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
     * @NoAdminRequired
     *
     */
    public function addMember($group_id){

        $member['name'] = 'Neu';
        $member['group_id'] = $group_id;

        $return = $this->db->addMember($member);

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
     * @NoAdminRequired
     *
     */
    public function editMember($member_id, $user_id, $name){
        $return = $this->db->editMember($member_id, $user_id, $name);
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
     * @NoAdminRequired
     *
     */
    public function deleteMember($member_id){
        $return = $this->db->deleteMember($member_id);
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
     * @NoAdminRequired
     *
     */
    public function editTimeline($id, $month, $version){
        $return = $this->db->editTimeline($id, $month, $version);
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
     * @NoAdminRequired
     *
     */
    public function deleteTimeline($id){
        $return = $this->db->deleteTimeline($id);
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

}

