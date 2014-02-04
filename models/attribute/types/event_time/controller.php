<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class EventTimeAttributeTypeController extends DateTimeAttributeTypeController {
 /* public function form() {
    $f = Loader::helper('form');
    $dtt = Loader::helper('form/date_time');
		echo $dtt->datetime('walkdate', $this->getStart() ? $this->getStart() : "2013-05-05 12:00:00");
echo $f->select('walkduration', array('60 minutes' => '1 hour', '90 minutes' => '1.5 hours', '120 minutes' => '2 hours', '150 minutes' => '2.5 hours', '180 minutes' => '3 hours', '210 minutes' => '3.5 hours'), $this->getDuration() ? $this->getDuration() : "60 minutes" );
 } */
  public function getScheduledEventTimes() {
    $db = Loader::db();
    $events = $db->GetAll("select start, end from atEventTime ev where ev.atScheduledId = ? order by ev.start", array($this->getAttributeValueID()));
  }

  public function getDurationsMinutes() {
    $db = Loader::db();
    return $db->GetAll("select TIMESTAMPDIFF(MINUTE,start,end) from atEventTime where atScheduleID = ?", array($this->getAttributeValueID()));
  }

  public function getDurationsHours() {
    $db = Loader::db();
    return $db->GetAll("select TIMESTAMPDIFF(HOUR,start,end) from atEventTime where atScheduleID = ?", array($this->getAttributeValueID()));
  }

  /* Will human-format here to simplify transfer to JSON response */
  public function getValue() {
    $db = Loader::db();
    $reArr = $db->GetRow("select open, type from atSchedule where avID = ?", array($this->getAttributeValueID()));
    if (sizeof($reArr) > 0) {
      /* Searched but couldn't find a prebuilt function - spent WAY too much time writing this. Bonus is, it runs much faster than PHP. */
      $reArr["slots"] = (object) $db->GetAll("select DATE_FORMAT(start, '%M %e, %Y') as date, DATE_FORMAT(start, '%h:%i %p') as time, 
        CONCAT( 
          CASE WHEN FLOOR(TIMESTAMPDIFF(HOUR,start,end)) > 0 THEN
          CONCAT((TIMESTAMPDIFF(HOUR,start,end)), 
      CASE 
      WHEN FLOOR(TIMESTAMPDIFF(HOUR,start,end)) = 1 THEN
      ' Hour'
      ELSE 
      ' Hours'
      END
    )
    ELSE 
    ''
    END,
    CASE
    WHEN MOD(TIMESTAMPDIFF(MINUTE,start,end),60) > 0 THEN
    CONCAT(
      CASE
      WHEN FLOOR(TIMESTAMPDIFF(HOUR,start,end)) > 0 THEN ', ' ELSE ''
      END
      , MOD(TIMESTAMPDIFF(MINUTE,start,end),60), ' Minutes')
      ELSE
      ''
      END
    ) as duration,
    DATE_FORMAT(start, '%Y-%m-%d %H:%i:%S') as eb_start,
    DATE_FORMAT(end, '%Y-%m-%d %H:%i:%S') as eb_end
    from atSchedule, atEventTime where atSchedule.avID = atEventTime.atScheduleID and atSchedule.avID = ? ORDER BY start < CURRENT_DATE, start", array($this->getAttributeValueID()));
      return array_filter($reArr);
    }
  }

  public function saveValue($data) {
    $db = Loader::db();
    $db->Replace('atSchedule', array('avID' => $this->getAttributeValueID(), 'open' => $data->open, 'type' => $data->type), 'avID', true);
    if($data->slots) {
      $this->setScheduledEventTimes($data->slots);
    }
  }

  /* $scheduledTimes = array of 'start' and 'duration' */
  public function setScheduledEventTimes($scheduledTimes) {
    $db = Loader::db();
    $db->BeginTrans();
    // $ok = $db->Execute("delete from atEventTime where atScheduleID = ?", [$this->getAttributeValueID()]);
    $ok = true;
    foreach( $scheduledTimes as $time ) {
      if ($ok) $ok = $db->AutoExecute("atEventTime", ["atScheduleID" => $this->getAttributeValueID(), "start" => date("Y-m-d H:i:s", strtotime($time->date . " " . $time->time)), "end" => date("Y-m-d H:i:s", strtotime($time->date . " " . $time->time . " + " . $time->duration))], "INSERT" );
    }
    if ($ok) $db->CommitTrans();
    else $db->RollbackTrans();
  }

  public function form() {
    $this->load();
    $data = $this->getValue();
    $dt = Loader::helper('form/date_time');
    $form = Loader::helper('form');
    $html = Loader::helper('html');
    $this->addHeaderItem($html->css('jquery.ui.css'));
    $this->addHeaderItem($html->javascript('jquery.ui.js'));
    /* TODO: look into putting this in a view php */
    print '<label class="checkbox">' . $form->checkbox('Open Booking', 'open', $data['open']) . ' Open Booking</label>'
      . '<label class="select">Booking Type' . $form->select('type', ['' => 'No Booking', 'set' => 'Scheduled', 'all' => 'By Request'], $data['type'] ) . '</label>';
    if($data['slots']) foreach($data['slots'] as $key=>$slot) {
      print "<label class='datetime'>Date #$key Start" . $dt->datetime("eventtime[$key][start]", str_replace('-','/',$slot['eb_start']))
        . "<label class='datetime'>Date #$key End" . $dt->datetime("eventtime[$key][end]", str_replace('-','/',$slot['eb_end']));
    }
  }

  public function saveForm($data) {
    $values = array();
/*    $f = Loader::helper('form');
    $dtt = Loader::helper('form/date_time');
    $values['open'] = $data->open
    $values['duration'] = $_POST['walkduration'];
    $this->saveValue($values); */
    var_dump($this->getValue());
  }
}

class EventTimeAttributeTypeValue extends Object {
}

