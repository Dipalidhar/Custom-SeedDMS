<?php

namespace MediaREPO\CalDAV\Backend;

use Sabre\VObject;
use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;

require_once "../inc/inc.ClassCalendar.php";

/**
 * MediaREPO CalDAV backend
 *
 * This backend is used to fetch calendar-data from MediaREPO
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MediaREPO extends \Sabre\CalDAV\Backend\AbstractBackend implements \Sabre\CalDAV\Backend\SyncSupport, \Sabre\CalDAV\Backend\SubscriptionSupport, \Sabre\CalDAV\Backend\SchedulingSupport {

    /**
     * We need to specify a max date, because we need to stop *somewhere*
     *
     * On 32 bit system the maximum for a signed integer is 2147483647, so
     * MAX_DATE cannot be higher than date('Y-m-d', 2147483647) which results
     * in 2038-01-19 to avoid problems when the date is converted
     * to a unix timestamp.
     */
    const MAX_DATE = '2038-01-01';

    /**
     * repo
     *
     * @var \mediarepo_Core_REPO
     */
    protected $repo;

    /**
     * The table name that will be used for calendars
     *
     * @var string
     */
    public $calendarTableName = 'calendars';

    /**
     * The table name that will be used for calendar objects
     *
     * @var string
     */
    public $calendarObjectTableName = 'calendarobjects';

    /**
     * The table name that will be used for tracking changes in calendars.
     *
     * @var string
     */
    public $calendarChangesTableName = 'calendarchanges';

    /**
     * The table name that will be used inbox items.
     *
     * @var string
     */
    public $schedulingObjectTableName = 'schedulingobjects';

    /**
     * The table name that will be used for calendar subscriptions.
     *
     * @var string
     */
    public $calendarSubscriptionsTableName = 'calendarsubscriptions';

    /**
     * List of CalDAV properties, and how they map to database fieldnames
     * Add your own properties by simply adding on to this array.
     *
     * Note that only string-based properties are supported here.
     *
     * @var array
     */
    public $propertyMap = [
        '{DAV:}displayname'                                   => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order'           => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'           => 'calendarcolor',
    ];

    /**
     * List of subscription properties, and how they map to database fieldnames.
     *
     * @var array
     */
    public $subscriptionPropertyMap = [
        '{DAV:}displayname'                                           => 'displayname',
        '{http://apple.com/ns/ical/}refreshrate'                      => 'refreshrate',
        '{http://apple.com/ns/ical/}calendar-order'                   => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'                   => 'calendarcolor',
        '{http://calendarserver.org/ns/}subscribed-strip-todos'       => 'striptodos',
        '{http://calendarserver.org/ns/}subscribed-strip-alarms'      => 'stripalarms',
        '{http://calendarserver.org/ns/}subscribed-strip-attachments' => 'stripattachments',
    ];

    /**
     * Creates the backend
     *
     * @param \mediarepo_Core_REPO $repo
     */
    function __construct(\mediarepo_Core_REPO $repo) {

        $this->repo = $repo;

    }

    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the calendar.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * Many clients also require:
     * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     * For this property, you can just return an instance of
     * Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet.
     *
     * If you return {http://sabredav.org/ns}read-only and set the value to 1,
     * ACL will automatically be put in read-only mode.
     *
     * @param string $principalUri
     * @return array
     */
    function getCalendarsForUser($principalUri) {

        $tmp = explode('/', $principalUri);
        if($tmp[0] != 'principals' || count($tmp) != 2)
            return [];

        $u = $this->repo->getUserByLogin($tmp[1]);
        if(!$u)
            return [];

        $calendars = [];

        $components = [];
//        if ($row['components']) {
 //           $components = explode(',', $row['components']);
  //      }

        $calendar = [
            'id' => "event-".$u->getID(),
            'uri' => 'events',
            'principaluri' => $principalUri,
            '{DAV:}displayname' => 'Kalendar',
            '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'Events added in MediaREPO',
            '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => '"'.time().'"',
            '{http://sabredav.org/ns}sync-token' => 46,
            '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(array('VEVENT')),
            '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp(1 ? 'transparent' : 'opaque'),
        ];


        foreach ($this->propertyMap as $xmlName => $dbName) {
//            $calendar[$xmlName] = $row[$dbName];
        }

        $calendars[] = $calendar;

        $calendar = [
            'id' => "todo-".$u->getID(),
            'uri' => 'todos',
            'principaluri' => $principalUri,
            '{DAV:}displayname' => 'Todo',
            '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'List of open tasks in MediaREPO',
            '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => '"'.time().'"',
            '{http://sabredav.org/ns}sync-token' => 46,
            '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(array('VTODO')),
            '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp(1 ? 'transparent' : 'opaque'),
        ];


        foreach ($this->propertyMap as $xmlName => $dbName) {
//            $calendar[$xmlName] = $row[$dbName];
        }

        $calendars[] = $calendar;


        return $calendars;

    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used
     * to reference this calendar in other methods, such as updateCalendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return string
     */
    function createCalendar($principalUri, $calendarUri, array $properties) {

        return null;

    }

    /**
     * Updates properties for a calendar.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param string $calendarId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
    function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {

        return;

    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    function deleteCalendar($calendarId) {

        return;

    }

    private function __getCalendarData($event) {
        $s = new \DateTime();
        $e = new \DateTime();
        $l = new \DateTime();
        $s->setTimestamp($event['start']);
        $e->setTimestamp($event['stop']);
        $l->setTimestamp($event['date']);
        $vcalendar = new VObject\Component\VCalendar([
            'VEVENT' => [
                'UID' => md5($event['userID'].$event['id']),
                'LAST-MODIFIED' => $l,
                'SUMMARY' => $event['name'],
                'DESCRIPTION' => $event['comment'],
                'DTSTART' => date('Ymd', $event['start']),
                'DTEND'   => date('Ymd', $event['stop'])
            ]
        ]);

        return $vcalendar->serialize();
    }

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can
     *     be any arbitrary string, but making sure it ends with '.ics' is a
     *     good idea. This is only the basename, or filename, not the full
     *     path.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * size - The size of the calendar objects, in bytes.
     *   * component - optional, a string containing the type of object, such
     *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
     *     the Content-Type header.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param string $calendarId
     * @return array
     */
    function getCalendarObjects($calendarId) {

        list($caltype, $userid) = explode('-', $calendarId);
        $result = [];
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
            $events = $calendar->getEventsInInterval(time(), time()+365*86400);

            foreach($events as $row) {
                $calendardata = self::__getCalendarData($row);
                $result[] = [
                    'id'           => $row['id'],
                    'uri'          => $row['id'],
                    'lastmodified' => time(), //$row['date'],
                    'etag'         => '"' . md5($calendardata) . '"',
                    'calendarid'   => $calendarId,
                    'size'         => strlen($calendardata),
                    'calendardata' => $calendardata,
                    'component'    => 'vevent', // can be vevent, vjournal, vtodo
                ];
            }
            break;
        case "todo":
            break;
        }
        return $result;

    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * This method must return null if the object did not exist.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return array|null
     */
    function getCalendarObject($calendarId, $objectUri) {

        list($caltype, $userid) = explode('-', $calendarId);
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
            $row = $calendar->getEvent($objectUri);
            if (!$row) return null;

            $calendardata = self::__getCalendarData($row);
            return [
                'id'            => $row['id'],
                'uri'           => $row['id'],
                'lastmodified'  => time(), //$row['date'],
                'etag'          => '"' . md5($calendardata) . '"',
                'calendarid'    => $calendarId,
                'size'          => (int)strlen($calendardata),
                'calendardata'  => $calendardata,
                'component'     => 'vevent',
            ];
            break;
        case "todo":
            return null;
        }

    }

    /**
     * Returns a list of calendar objects.
     *
     * This method should work identical to getCalendarObject, but instead
     * return all the calendar objects in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $calendarId
     * @param array $uris
     * @return array
     */
    function getMultipleCalendarObjects($calendarId, array $uris) {

        list($caltype, $userid) = explode('-', $calendarId);
        $result = [];
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
    //        $events = $calendar->getEventsInInterval(time(), time()+365*86400);

            foreach($uris as $uri) {
                $row = $calendar->getEvent((int)$uri);
                $calendardata = self::__getCalendarData($row);
                $result[] = [
                    'id'           => $row['id'],
                    'uri'          => $row['id'],
                    'lastmodified' => time(), //$row['date'],
                    'etag'         => '"' . md5($calendardata) . '"',
                    'calendarid'   => $calendarId,
                    'size'         => strlen($calendardata),
                    'calendardata' => $calendardata,
                    'component'    => 'vevent', // can be vevent, vjournal, vtodo
                ];
            }
            break;
        case "todo":
            break;
        }
        return $result;

    }


    /**
     * Creates a new calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function createCalendarObject($calendarId, $objectUri, $calendarData) {

        list($caltype, $userid) = explode('-', $calendarId);
        $result = [];
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $extraData = $this->getDenormalizedData($calendarData);

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
            if($eventid = $calendar->addEvent($extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['summary'], $extraData['description'])) {
                return '"' . $extraData['etag'] . '"';
            }
            break;
        case "todo":
            break;
        }

        return null;

    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function updateCalendarObject($calendarId, $objectUri, $calendarData) {

        list($caltype, $userid) = explode('-', $calendarId);
        $result = [];
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $extraData = $this->getDenormalizedData($calendarData);

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
            if($eventid = $calendar->editEvent((int) $objectUri, $extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['summary'], $extraData['description'])) {
                return '"' . $extraData['etag'] . '"';
            }
            break;
        case "todo":
            break;
        }

        return null;

    }

    /**
     * Parses some information from calendar objects, used for optimized
     * calendar-queries.
     *
     * Returns an array with the following keys:
     *   * etag - An md5 checksum of the object without the quotes.
     *   * size - Size of the object in bytes
     *   * componentType - VEVENT, VTODO or VJOURNAL
     *   * firstOccurence
     *   * lastOccurence
     *   * uid - value of the UID property
     *
     * @param string $calendarData
     * @return array
     */
    protected function getDenormalizedData($calendarData) {

        $vObject = VObject\Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $firstOccurence = null;
        $lastOccurence = null;
        $uid = null;
        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                $uid = (string)$component->UID;
                break;
            }
        }
        if (!$componentType) {
            throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }
        if ($componentType === 'VEVENT') {
            $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
            // Finding the last occurence is a bit harder
            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $lastOccurence = $endDate->getTimeStamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $lastOccurence = $endDate->getTimeStamp();
                } else {
                    $lastOccurence = $firstOccurence;
                }
            } else {
                $it = new VObject\Recur\EventIterator($vObject, (string)$component->UID);
                $maxDate = new \DateTime(self::MAX_DATE);
                if ($it->isInfinite()) {
                    $lastOccurence = $maxDate->getTimeStamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $maxDate) {
                        $end = $it->getDtEnd();
                        $it->next();

                    }
                    $lastOccurence = $end->getTimeStamp();
                }

            }
            if (isset($component->SUMMARY)) {
                $summary = (string) $component->SUMMARY;
            }
            if (isset($component->DESCRIPTION)) {
                $description = (string) $component->DESCRIPTION;
            }
        }

        return [
            'etag'           => md5($calendarData),
            'size'           => strlen($calendarData),
            'componentType'  => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence'  => $lastOccurence,
            'summary'  => $summary,
            'description'  => $description,
            'uid'            => $uid,
        ];

    }

    /**
     * Deletes an existing calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return void
     */
    function deleteCalendarObject($calendarId, $objectUri) {

        list($caltype, $userid) = explode('-', $calendarId);
        $result = [];
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
            $calendar->delEvent((int) $objectUri);
            break;
        case "todo":
            break;
        }

        return;

    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly adviced to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interprete all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param string $calendarId
     * @param array $filters
     * @return array
     */
    function calendarQuery($calendarId, array $filters) {

        list($caltype, $userid) = explode('-', $calendarId);
        $events = array();
        switch($caltype) {
        case "event":
            if(!($u = $this->repo->getUser($userid)))
                return [];

            if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
                $componentType = $filters['comp-filters'][0]['name'];
                if ($componentType == 'VEVENT' && isset($filters['comp-filters'][0]['time-range'])) {
                    $timeRange = $filters['comp-filters'][0]['time-range'];

                    if ($timeRange && $timeRange['start']) {
                        $startdate = $timeRange['start']->getTimeStamp();
                    } else {
                        $startdate = time();
                    }
                    if ($timeRange && $timeRange['end']) {
                        $enddate = $timeRange['end']->getTimeStamp();
                    } else {
                        $enddate = time()+365*86400;
                    }
                    $calendar = new \mediarepo_Calendar($this->repo->getDB(), $u);
                    $events = $calendar->getEventsInInterval($startdate, $enddate);
                }
            }
            break;
        case "todo":
            break;
        }

        $result = [];
        foreach($events as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    /**
     * Searches through all of a users calendars and calendar objects to find
     * an object with a specific UID.
     *
     * This method should return the path to this object, relative to the
     * calendar home, so this path usually only contains two parts:
     *
     * calendarpath/objectpath.ics
     *
     * If the uid is not found, return null.
     *
     * This method should only consider * objects that the principal owns, so
     * any calendars owned by other principals that also appear in this
     * collection should be ignored.
     *
     * @param string $principalUri
     * @param string $uid
     * @return string|null
     */
    function getCalendarObjectByUID($principalUri, $uid) {

        return null;

    }

    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified calendar.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property this is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param string $calendarId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return array
     */
    function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        return [];

    }

    /**
     * Adds a change record to the calendarchanges table.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param int $operation 1 = add, 2 = modify, 3 = delete.
     * @return void
     */
    protected function addChange($calendarId, $objectUri, $operation) {

        return;

    }

    /**
     * Returns a list of subscriptions for a principal.
     *
     * Every subscription is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    subscription. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the subscription.
     *  * principaluri. The owner of the subscription. Almost always the same as
     *    principalUri passed to this method.
     *  * source. Url to the actual feed
     *
     * Furthermore, all the subscription info must be returned too:
     *
     * 1. {DAV:}displayname
     * 2. {http://apple.com/ns/ical/}refreshrate
     * 3. {http://calendarserver.org/ns/}subscribed-strip-todos (omit if todos
     *    should not be stripped).
     * 4. {http://calendarserver.org/ns/}subscribed-strip-alarms (omit if alarms
     *    should not be stripped).
     * 5. {http://calendarserver.org/ns/}subscribed-strip-attachments (omit if
     *    attachments should not be stripped).
     * 7. {http://apple.com/ns/ical/}calendar-color
     * 8. {http://apple.com/ns/ical/}calendar-order
     * 9. {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     *    (should just be an instance of
     *    Sabre\CalDAV\Property\SupportedCalendarComponentSet, with a bunch of
     *    default components).
     *
     * @param string $principalUri
     * @return array
     */
    function getSubscriptionsForUser($principalUri) {

        return [];

    }

    /**
     * Creates a new subscription for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this subscription in other methods, such as updateSubscription.
     *
     * @param string $principalUri
     * @param string $uri
     * @param array $properties
     * @return mixed
     */
    function createSubscription($principalUri, $uri, array $properties) {

        return null;
    }

    /**
     * Updates a subscription
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param mixed $subscriptionId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
    function updateSubscription($subscriptionId, DAV\PropPatch $propPatch) {

        return;
    }

    /**
     * Deletes a subscription
     *
     * @param mixed $subscriptionId
     * @return void
     */
    function deleteSubscription($subscriptionId) {

        return;

    }

    /**
     * Returns a single scheduling object.
     *
     * The returned array should contain the following elements:
     *   * uri - A unique basename for the object. This will be used to
     *           construct a full uri.
     *   * calendardata - The iCalendar object
     *   * lastmodified - The last modification date. Can be an int for a unix
     *                    timestamp, or a PHP DateTime object.
     *   * etag - A unique token that must change if the object changed.
     *   * size - The size of the object, in bytes.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @return array
     */
    function getSchedulingObject($principalUri, $objectUri) {

        $stmt = $this->pdo->prepare('SELECT uri, calendardata, lastmodified, etag, size FROM ' . $this->schedulingObjectTableName . ' WHERE principaluri = ? AND uri = ?');
        $stmt->execute([$principalUri, $objectUri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'uri'          => $row['uri'],
            'calendardata' => $row['calendardata'],
            'lastmodified' => $row['lastmodified'],
            'etag'         => '"' . $row['etag'] . '"',
            'size'         => (int)$row['size'],
         ];

    }

    /**
     * Returns all scheduling objects for the inbox collection.
     *
     * These objects should be returned as an array. Every item in the array
     * should follow the same structure as returned from getSchedulingObject.
     *
     * The main difference is that 'calendardata' is optional.
     *
     * @param string $principalUri
     * @return array
     */
    function getSchedulingObjects($principalUri) {

        return [];

        $stmt = $this->pdo->prepare('SELECT id, calendardata, uri, lastmodified, etag, size FROM ' . $this->schedulingObjectTableName . ' WHERE principaluri = ?');
        $stmt->execute([$principalUri]);

        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'calendardata' => $row['calendardata'],
                'uri'          => $row['uri'],
                'lastmodified' => $row['lastmodified'],
                'etag'         => '"' . $row['etag'] . '"',
                'size'         => (int)$row['size'],
            ];
        }

        return $result;

    }

    /**
     * Deletes a scheduling object
     *
     * @param string $principalUri
     * @param string $objectUri
     * @return void
     */
    function deleteSchedulingObject($principalUri, $objectUri) {

        return;
        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->schedulingObjectTableName . ' WHERE principaluri = ? AND uri = ?');
        $stmt->execute([$principalUri, $objectUri]);

    }

    /**
     * Creates a new scheduling object. This should land in a users' inbox.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @param string $objectData
     * @return void
     */
    function createSchedulingObject($principalUri, $objectUri, $objectData) {

        return;
        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->schedulingObjectTableName . ' (principaluri, calendardata, uri, lastmodified, etag, size) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$principalUri, $objectData, $objectUri, time(), md5($objectData), strlen($objectData) ]);

    }

}
