<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

$string['pluginname'] = 'Ticket Tracker/User support';
$string['pluginadministration'] = 'Tracker administration';

// Capabilities
$string['tracker:addinstance'] = 'Add a tracker';
$string['tracker:canbecced'] = 'Can be choosen for cc';
$string['tracker:comment'] = 'Comment issues';
$string['tracker:configure'] = 'Configure tracker options';
$string['tracker:configurenetwork'] = 'Configure network features';
$string['tracker:develop'] = 'Be choosen to resolve tickets';
$string['tracker:editcomment'] = 'Edit issue comments';
$string['tracker:manage'] = 'Manage issues';
$string['tracker:managepriority'] = 'Manage priority of entries';
$string['tracker:managewatches'] = 'Manage watches on ticket';
$string['tracker:report'] = 'Report tickets';
$string['tracker:resolve'] = 'Resolve tickets';
$string['tracker:seeissues'] = 'See issue content';
$string['tracker:shareelements'] = 'Share elements at site level';
$string['tracker:viewallissues'] = 'See all tickets';
$string['tracker:viewpriority'] = 'View priority of my owned tickets';
$string['tracker:viewreports'] = 'View issue work reports';
$string['tracker:otherscomments'] = 'View comments by others';
$string['tracker:reportpastdue'] = 'Report tickets after duedate';

$string['AND'] = 'AND';
$string['IN'] = 'IN';
$string['abandonned'] = 'Abandonned';
$string['action'] = 'Action';
$string['active'] = 'Active in form';
$string['activeplural'] = 'Actives';
$string['addacomment'] = 'Add a comment';
$string['addanoption'] = 'Add an option';
$string['addaquerytomemo'] = 'Add this search query to "my queries"';
$string['addawatcher'] = 'Add a watcher';
$string['addtothetracker'] = 'Add to this tracker';
$string['administration'] = 'Administration';
$string['administrators'] = 'Administrators';
$string['alltracks'] = 'Watch my work in all trackers';
$string['any'] = 'All';
$string['askraise'] = 'Ask resolvers to raise priority';
$string['assignedto'] = 'Assigned to';
$string['assignee'] = 'Assignee';
$string['assigns'] = 'Random Assign';
$string['assignmethod'] = 'Assign method';
$string['assignmethod_help'] = '
How the random assignation process will operate.

* <strong>Per reviewer:</strong> N issues (choosen randomly) will be assigned to each reviewer.
* <strong>Per issue:</strong> N reviewers (choosen randomly) will be assigned to each issue.

Care wil be taken to start with reviewers and issues with less assignations so each reviewer/issue will have more or less the same charge.
No reviewer will be assigned to thet own issues.
';
$string['assigndeveloper'] = 'per reviewer';
$string['assignissue'] = 'per issue';
$string['removeassigns'] = 'Remove existing assignees';
$string['randomassignsdone'] = '{$a} random assignations done';

$string['attributes'] = 'Attributes';
$string['autourl'] = 'Automatic Url Recollection';
$string['backtocourse'] = 'Back to course';
$string['browse'] = 'Browse';
$string['browser'] = 'Browser';
$string['build'] = 'Version';
$string['by'] = '<i>assigned by</i>';
$string['captcha'] = 'Captcha'; // @DYNA
$string['cascade'] = 'Send upper level';
$string['cascadedticket'] = 'Transferred from: ';
$string['cced'] = 'Cced';
$string['ccs'] = 'Ccs';
$string['checkbox'] = 'Checkbox'; // @DYNA
$string['checkboxhoriz'] = 'Checkbox horizontal'; // @DYNA
$string['chooselocal'] = 'Choose a local tracker as parent';
$string['chooseremote'] = 'Choose a remote host';
$string['chooseremoteparent'] = 'Choose a remote instance';
$string['choosetarget'] = 'Choose target';
$string['clearsearch'] = 'Clear search criteria';
$string['comment'] = 'Comment';
$string['commentedby'] = 'Commented by';
$string['comments'] = 'Comments';
$string['component'] = 'Component';
$string['count'] = 'Count';
$string['countbyassignee'] = 'By assignee';
$string['countbymonth'] = 'By monthly creation report';
$string['countbyreporter'] = 'By reporter';
$string['countbystate'] = 'Report by status';
$string['constant'] = 'Constant';
$string['constantinfosource'] = 'Constant info source';
$string['customconstant'] = 'Custom value';
$string['constantsiteshortname'] = 'Site shortname';
$string['constantsitefullname'] = 'Site fullname';
$string['constantcurrentidnumber'] = 'Current user ID Number';
$string['constantcurrentcourseidnumber'] = 'Current course ID number';
$string['constantcurrentcourseshortname'] = 'Current ocurse shortname';
$string['constantcurrentcoursefullname'] = 'Current course fullname';
$string['createdinmonth'] = 'Created in current month';
$string['createnewelement'] = 'Create a new element';
$string['currentbinding'] = 'Current cascade';
$string['database'] = 'Database';
$string['datereported'] = 'Report date';
$string['defaultassignee'] = 'Default assignee';
$string['deleteattachedfile'] = 'Delete attachement';
$string['dependancies'] = 'Dependencies';
$string['dependson'] = 'Depends on ';
$string['distribute'] = 'Move the ticket to another tracker';
$string['descriptionisempty'] = 'Description is empty';
$string['distribute'] = 'Move the ticket to another tracker';
$string['doaddelementautourl'] = 'Add an url collector'; // @DYNA
$string['doaddelementcheckbox'] = 'Add a checkbox element'; // @DYNA
$string['doaddelementcheckboxhoriz'] = 'Add a checkbox element'; // @DYNA
$string['doaddelementdropdown'] = 'Add a dropdown element'; // @DYNA
$string['doaddelementfile'] = 'Add an attachment file element'; // @DYNA
$string['doaddelementradio'] = 'Add a radio element'; // @DYNA
$string['doaddelementradiohoriz'] = 'Add a radio element'; // @DYNA
$string['doaddelementtext'] = 'Add a text field'; // @DYNA
$string['doaddelementtextarea'] = 'Add a text area'; // @DYNA
$string['doupdateelementautourl'] = 'Update an auto url'; // @DYNA
$string['doupdateelementcheckbox'] = 'Update a checkbox element'; // @DYNA
$string['doupdateelementcheckboxhoriz'] = 'Update a checkbox element'; // @DYNA
$string['doupdateelementdropdown'] = 'Update a dropdown element';// @DYNA
$string['doupdateelementfile'] = 'Update a attachment file element'; // @DYNA
$string['doupdateelementradio'] = 'Update a radio button element'; // @DYNA
$string['doupdateelementradiohoriz'] = 'Update a radio button element'; // @DYNA
$string['doupdateelementtext'] = 'Update a text field'; // @DYNA
$string['doupdateelementtextarea'] = 'Update a text area'; // @DYNA
$string['dropdown'] = 'Dropdown';
$string['editelement'] = 'Update Form Element';
$string['editoptions'] = 'Update options';
$string['editproperties'] = 'Update properties';
$string['editquery'] = 'Change a stored query';
$string['editwatch'] = 'Change a cc registering';
$string['elements'] = 'Available elements';
$string['elementsused'] = 'Used elements';
$string['elucidationratio'] = 'Elucidation ratio';
$string['emailoptions'] = 'Mail options';
$string['emergency'] = 'Urgent query';
$string['emptydefinition'] = 'Target tracker has no definition.';
$string['enablecomments'] = 'Allow comments';
$string['enablecomments_help'] = 'When this option is enabled, readers of issue records can add comments in the tracker.';
$string['errorcaptcha'] = 'You failed givieng back the captcha. Or maybe are you a robot?';
$string['erroraddissueattribute'] = 'Could not submit issue(s) attribute(s). Case {$a} ';
$string['erroralreadyinuse'] = 'Element already in use';
$string['errorannotdeletecarboncopies'] = 'Cannot delete carbon copies for user : {$a}';
$string['errorannotdeletequeryid'] = 'Cannot delete query id: {$a]';
$string['errorbadlistformat'] = 'Only numbers (or a list of numbers seperated by a comma (",") allowed in the issue number field';
$string['errorcannotaddelementtouse'] = 'Cannot add element to list of elements to use for this tracker';
$string['errorcannotclearelementsforissue'] = 'Could not clear elements for issue {$a}';
$string['errorcannotcreateelementoption'] = 'Could not create element option';
$string['errorcannotdeletearboncopyforuser'] = 'Cannot delete carbon copy {$a->issue} for user : {$a->userid}';
$string['errorcannotdeletecc'] = 'Cannot delete carbon copy';
$string['errorcannotdeleteelement'] = 'Cannot delete element from list of elements to use for this tracker';
$string['errorcannotdeleteelementtouse'] = 'Cannot delete element from list of elements to use for this tracker';
$string['errorcannotdeleteolddependancy'] = 'Could not delete old dependancies';
$string['errorcannotdeleteoption'] = 'Error trying to delete element option';
$string['errorcannoteditwatch'] = 'Cannot edit this watch';
$string['errorcannothideelement'] = 'Cannot hide element from form for this tracker';
$string['errorcannotlogoldownership'] = 'Could not log old ownership';
$string['errorcannotsaveprefs'] = 'Could not insert preference record';
$string['errorcannotsetparent'] = 'Cannot set parent in this tracker';
$string['errorcannotshowelement'] = 'Cannot show element in form for this tracker';
$string['errorcannotsubmitticket'] = 'Error registering new ticket';
$string['errorcannotujpdateoptionbecauseused'] = 'Cannot update the element option because it is currently being used as a attribute for an issue';
$string['errorcannotunbindparent'] = 'Cannot unbind parent of this tracker';
$string['errorcannotupdateelement'] = 'Could not update element';
$string['errorcannotupdateissuecascade'] = 'Could not update issue for cascade';
$string['errorcannotupdateprefs'] = 'Could not update preference record';
$string['errorcannotupdatetrackerissue'] = 'Could not update tracker issue';
$string['errorcannotupdatewatcher'] = 'Could not update watcher';
$string['errorcannotviewelementoption'] = 'Cannot view element options';
$string['errorcannotwritecomment'] = 'Error writing comment';
$string['errorcannotwritedependancy'] = 'Could not write dependancy record';
$string['errorcanotaddelementtouse'] = 'Cannot add element to list of elements to use for this tracker';
$string['errorcookie'] = 'Failed to set cookie: {$a} .';
$string['errorcoursemisconfigured'] = 'Course is misconfigured';
$string['errorcoursemodid'] = 'Course Module ID was incorrect';
$string['errordbupdate'] = 'Could not update element';
$string['errorelementdoesnotexist'] = 'Element does not exist';
$string['errorelementinuse'] = 'Element already in use';
$string['errorfindingaction'] = 'Error:  Cannot find action: {$a}';
$string['errorinvalidtrackerelementid'] = 'Invalid element. Cannot edit element id';
$string['errormoduleincorrect'] = 'Course module is incorrect';
$string['errornoaccessallissues'] = 'You do not have access to view all issues.';
$string['errornoaccessissue'] = 'You do not have access to view this issue.';
$string['errornoeditissue'] = 'You do not have access to edit this issue.';
$string['errorrecordissue'] = 'Could not submit issue';
$string['errorremote'] = 'Error on remote side<br/> {$a} ';
$string['errorremote'] = 'Remote error: {$a}';
$string['errorremotesendingcascade'] = 'Error on sending cascade :<br/> {$a}';
$string['errorunabletosabequery'] = 'Unable to save query as query';
$string['errorunabletosavequeryid'] = 'Unable to update query id {$a}';
$string['errorupdateelement'] = 'Could not update element';
$string['eventcourse_module_edited'] = 'Tracker edited';
$string['eventcourse_module_list_viewed'] = 'Trackers listed';
$string['eventcourse_module_viewed'] = 'Tracker entered';
$string['event_tracker_issue_commented'] = 'Tracker Issue commented';
$string['event_tracker_issue_reported'] = 'Tracker Issue reported';
$string['evolution'] = 'Trends';
$string['evolutionbymonth'] = 'Issue state evolution';
$string['failovertrackerurl'] = 'Fail over tracker url';
$string['file'] = 'Attached file';
$string['follow'] = 'Follow';
$string['generaltrend'] = 'Trend';
$string['gotooriginal'] = 'Go to original ticket';
$string['gototransfered'] = 'Go to transfered ticket';
$string['hassolution'] = 'A solution is published for this issue';
$string['hideccs'] = 'Hide watchers';
$string['hidecomments'] = 'Hide comments';
$string['hidedependancies'] = 'Hide dependancies';
$string['hidehistory'] = 'Hide history';
$string['history'] = 'Assignees';
$string['iamadeveloper'] = 'I can work on tickets';
$string['iamnotadeveloper'] = 'I cannot work on tickets';
$string['icanmanage'] = 'I can manage ticket content';
$string['icannotmanage'] = 'I cannot manage ticket content';
$string['icannotreport'] = 'I cannot report';
$string['icannotresolve'] = 'I cannot assign nor close tickets';
$string['icanreport'] = 'I can report';
$string['icanresolve'] = 'I can assign and close tickets';
$string['id'] = 'Identifier';
$string['intest'] = 'Testing';
$string['intro'] = 'Description';
$string['inworkinmonth'] = 'Still in work';
$string['issueid'] = 'Ticket';
$string['issuename'] = 'Ticket label ';
$string['issuenumber'] = 'Ticket';
$string['issues'] = 'ticket records';
$string['issuestoassign'] = 'Tickets to assign: {$a}';
$string['issuestowatch'] = 'Tickets to watch: {$a}';
$string['knownelements'] = 'Known tracker form elements';
$string['listissues'] = 'List view';
$string['local'] = 'Local';
$string['lowerpriority'] = 'Lower priority';
$string['lowertobottom'] = 'Lower to basement';
$string['manageelements'] = 'Manage elements';
$string['managenetwork'] = 'Cascade and network setup';
$string['manager'] = 'Manager';
$string['mandatory'] = 'Mandatory answer';
$string['me'] = 'My profile';
$string['menumultiple'] = 'Set as multi-select';
$string['message_bugtracker'] = 'Thanks for your contribution and helping making this service more reliable.';
$string['message_taskspread'] = 'You just defined a task. Don\'t foget assigning it to some recepient in the nxt screns to distribute it.';
$string['message_ticketting'] = 'We have registered your query. I has been assigned to {$a}.';
$string['message_ticketting_preassigned'] = 'We have registered your query. It will be assigned and handled as soon as possible.';
$string['mode_bugtracker'] = 'Team bug tracker';
$string['mode_customized'] = 'Customized tracker';
$string['mode_taskspread'] = 'Task distributor';
$string['mode_ticketting'] = 'User support ticketting';
$string['modulename'] = 'User support - Tracker';
$string['modulenameplural'] = 'User support - trackers';
$string['month'] = 'Month';
$string['myassignees'] = 'Resolver I assigned';
$string['myissues'] = 'Tickets I resolve';
$string['mypreferences'] = 'My preferences';
$string['myprofile'] = 'My profile';
$string['myqueries'] = 'My queries';
$string['mytasks'] = 'My tickets';
$string['mytickets'] = 'My tickets';
$string['mywatches'] = 'My watches';
$string['mywork'] = 'My work';
$string['name'] = 'Name';
$string['namecannotbeblank'] = 'Name cannot be empty';
$string['networkable'] = 'Network open';
$string['newissue'] = 'New ticket';
$string['noassignedtickets'] = 'No assigned tickets';
$string['noassignees'] = 'No assignee';
$string['nochange'] = 'Leave unchanged';
$string['nocomments'] = 'No comments';
$string['nodata'] = 'No data to show.';
$string['nodevelopers'] = 'No developpers';
$string['noelements'] = 'No element';
$string['noelementscreated'] = 'No element created';
$string['nofile'] = 'No uploaded file';
$string['nofileloaded'] = 'No file loaded here.';
$string['noissuesreported'] = 'No ticket here';
$string['noissuesresolved'] = 'No resolved ticket';
$string['nolocalcandidate'] = 'No local candidate for cascading';
$string['nomnet'] = 'Moodle network seems disabled';
$string['nooptions'] = 'No option';
$string['noqueryssaved'] = 'No stored query';
$string['noremotehosts'] = 'No network host available';
$string['noremotetrackers'] = 'No remote tracker available';
$string['noreporters'] = 'No reporters, there are probably no issues entered here.';
$string['noresolvers'] = 'No resolvers';
$string['noresolvingissue'] = 'No ticket assigned';
$string['notickets'] = 'No owned tickets.';
$string['noticketsorassignation'] = 'No tickets or assignations';
$string['notifications'] = 'Notifications';
$string['notifications_help'] = 'This parameter enables or disables mail notifications from the Tracker. If enabled, some events or state changes within the tracker will trigger mail messages to the concerned users.';
$string['notrackeradmins'] = 'No admins';
$string['notrackers'] = 'No trackers in this course.';
$string['nowatches'] = 'No watches';
$string['numberofissues'] = 'Ticket count';
$string['observers'] = 'Observers';
$string['on'] = 'on';
$string['open'] = 'Open';
$string['option'] = 'Option ';
$string['optionisused'] = 'This options id already in use for this element.';
$string['options'] = 'Options';
$string['order'] = 'Order';
$string['originalticketnoaccess'] = 'This ticket is transfered from another ticket you do not have read access on.';
$string['pages'] = 'Pages';
$string['posted'] = 'Posted';
$string['potentialresolvers'] = 'Potential resolvers';
$string['preferences'] = 'Preferences';
$string['prefsnote'] = 'Preferences setups which default notifications you may receive when creating a new entry or when you register a watch for an existing issue';
$string['print'] = 'Print';
$string['priority'] = 'Attributed Priority';
$string['priorityid'] = 'Priority';
$string['private'] = 'Private info';
$string['profile'] = 'User settings';
$string['published'] = 'Published';
$string['queries'] = 'Queries';
$string['query'] = 'Query';
$string['queryname'] = 'Query label';
$string['radio'] = 'Radio buttons'; // @DYNA
$string['radiohoriz'] = 'Horizontal radio buttons'; // @DYNA
$string['raisepriority'] = 'Raise priority';
$string['raiserequestcaption'] = 'Priority raising request for a ticket';
$string['raiserequesttitle'] = 'Ask for raising priority';
$string['raisetotop'] = 'Raise to ceiling';
$string['reason'] = 'Reason';
$string['register'] = 'Watch this ticket';
$string['reportanissue'] = 'Post a ticket';
$string['reportedby'] = 'Reported by';
$string['reporter'] = 'Reporter';
$string['reports'] = 'Reports';
$string['resolution'] = 'Solution';
$string['resolved'] = 'Resolved';
$string['resolvedplural'] = 'Resolved';
$string['resolvedplural'] = 'Resolved';
$string['resolvedplural2'] = 'Resolved';
$string['resolver'] = 'My issues';
$string['resolvers'] = 'Resolvers';
$string['resolving'] = 'Resolving';
$string['runninginmonth'] = 'Running in current month';
$string['saveasquery'] = 'Save a query';
$string['savequery'] = 'Save the query';
$string['search'] = 'Search';
$string['searchbyid'] = 'Search by ID';
$string['searchcriteria'] = 'Search criteria';
$string['searchresults'] = 'Search results';
$string['searchwiththat'] = 'Launch this query again';
$string['selectparent'] = 'Parent selection';
$string['sendrequest'] = 'Send request';
$string['setoncomment'] = 'Send me the coments';
$string['setactive'] = 'Turn active ';
$string['setinactive'] = 'Do not show on form ';
$string['setinactive'] = 'Show on form ';
$string['setmandatory'] = 'Set data required ';
$string['setnotmandatory'] = 'Set data not required ';
$string['setoncomment'] = 'Send me the coments';
$string['setprivate'] = 'Set info private to developers ';
$string['setpublic'] = 'Set info public ';
$string['setwhenopens'] = 'Don\'t advise me when opens';
$string['setwhenpublished'] = 'Don\'t advise me when solution is published';
$string['setwhenresolves'] = 'Don\'t advise me when resolves';
$string['setwhentesting'] = 'Don\'t advise me when a solution is tested';
$string['setwhenthrown'] = 'Don\'t advise me when is abandonned';
$string['setwhenwaits'] = 'Don\'t advise me when waits';
$string['setwhenworks'] = 'Don\'t advise me when on work';
$string['sharethiselement'] = 'Turn this element sitewide';
$string['sharing'] = 'Sharing';
$string['showccs'] = 'Show watchers';
$string['showcomments'] = 'Show comments';
$string['showdependancies'] = 'Show dependancies';
$string['showhistory'] = 'Show history';
$string['site'] = 'Site';
$string['solution'] = 'Solution';
$string['sortorder'] = 'Order';
$string['standalone'] = 'Standalone tracker (top level support).';
$string['statehistory'] = 'States';
$string['stateprofile'] = 'Ticket states';
$string['status'] = 'Status';
$string['strictworkflow'] = 'Strict workflow';
$string['submission'] = 'A new ticket is reported in tracker [{$a}]';
$string['submitbug'] = 'Submit the ticket';
$string['subtrackers'] = 'Subtrackers';
$string['sum_opened'] = 'Opened';
$string['sum_posted'] = 'Waiting';
$string['sum_reported'] = 'Reported';
$string['sum_resolved'] = 'Solved';
$string['summaryadmin'] = 'Summary';
$string['summary'] = 'Summary';
$string['supportmode'] = 'Support mode';
$string['testing'] = 'Being tested';
$string['text'] = 'Textfield'; // @DYNA
$string['textarea'] = 'Textarea'; // @DYNA
$string['thanksdefault'] = 'Thanks to contributing to the constant enhancement of this service.';
$string['thanksmessage'] = 'Thanks message.';
$string['ticketprefix'] = 'Ticket prefix';
$string['tickets'] = 'Tickets';
$string['trackerissuereported'] = '{$a->shortname}: Reported and assigned an issue on Tracker "{$a->name}".';
$string['trackerissuecommented'] = '{$a->shortname}: Added a comment on Tracker "{$a->name}".';
$string['tracker-levelaccess'] = 'My capabilities in this tracker';
$string['tracker_name'] = 'Tracker module services';
$string['tracker_service_name'] = 'Tracker module services';
$string['trackerelements'] = 'Tracker\'s definition';
$string['trackereventchanged'] = '{$a->shortname}: Issue state change in tracker "{$a->name}".';
$string['trackerhost'] = 'Parent host for tracker';
$string['trackername'] = 'Tracker name';
$string['transfer'] = 'Transfered';
$string['transfered'] = 'Transfered';
$string['transferedticketnoaccess'] = 'This ticket is transfered to another ticket you do not have read access on.';
$string['transferservice'] = 'Support ticket cascading';
$string['turneditingoff'] = 'Turn editing off';
$string['turneditingon'] = 'Turn editing on';
$string['type'] = 'Type';
$string['unassigned'] = 'Unassigned' ;
$string['unbind'] = 'Unbind cascade';
$string['unmatchingelements'] = 'Both tracker definition do not match. This may result in unexpected behaviour when cascading support tickets.';
$string['unregisterall'] = 'Unregister from all' ;
$string['unsetoncomment'] = 'Advise me when posting comments';
$string['unsetwhenopens'] = 'Advise me when opens';
$string['unsetwhenpublished'] = 'Advise me when solution is published';
$string['unsetwhenresolves'] = 'Advise me when resolves';
$string['unsetwhentesting'] = 'Advise me when a solution is tested';
$string['unsetwhenthrown'] = 'Advise me when is thrown';
$string['unsetwhenwaits'] = 'Advise me when waits';
$string['unsetwhenworks'] = 'Advise me when got working';
$string['urgentraiserequestcaption'] = 'A user has requested an urgent priority demand';
$string['urgentsignal'] = 'URGENT QUERY';
$string['validated'] = 'Validated';
$string['view'] = 'Views';
$string['vieworiginal'] = 'See original';
$string['voter'] = 'Vote';
$string['waiting'] = 'Waiting';
$string['watches'] = 'Watches';
$string['youneedanaccount'] = 'You need an authorized account here to report a ticket';

// help strings

$string['tracker_description'] = '<p>When publishing this service, you allow trackers from {$a} to cascade the support tickets to a local tracker.</p>
<ul><li><i>Depends on</i>: You have to suscribe {$a} to this service.</li></ul>
<p>Suscribing to this service allows local trackers to send support tickets to some tracker in {$a}.</p>
<ul><li><i>Depends on</i>: You have to publish this service on {$a}.</li></ul>';

$string['supportmode_help'] = 'Support mode applies some predefined settings and role overides on the tracker to achieved a preset behaviour.

* Bug report: Reporters have access to the whole ticket list for reading the issues in a collaborative way. All states are enabled for a complete
tecnhical operation workflow, including operations on preprod test systems.

* User support/Ticketting: Reporters usually have only access to the tickets they have posted and cannot access to the ticket browsing mode. Some states
have been disabled, that are more commonly used for technical operations.

* Task distribution: Reporters can have or not access to the whole distributed ticket list. Workers can only have access to the tickets they are asigned to
through the "My work" screen. They will NOT have access to the browse function. some intermediate states have beed disabled for a simpler marking of task states.

* Customized: When customized, the activity editor can choose states and overrides to apply to the tracker. This is the most flexible setting, but needs a correct knowledge of Moodle roles and setting management.

';

$string['modulename_help'] = 'The Tracker activity allows tracking tickets for help, bug report, or also trackable activities in a course.

The activity allows creating the tracking form with attributes elements from a list of configurable elements. Some elements can be shared at site
level to be reused in other trackers.

the ticket (or task) can be assigned for work to another user.

The tracked ticket is a statefull ticket that sends state change notifications to any follower that has enabled notifications. A user can choose which state changes he tracks usually.

Tickets can be chained in dependancy, so it may be easy to follow a cause/consequence ticket sequence.

History of changes are tracked for each ticket.

Ticket tracker can be cascaded locally or through MNET allowing a ticket manager to send a ticket to a remote (higher level) ticket collector.

Trackers can now be chained so that ticket can be moved between trackers.
';

$string['elements_help'] = '
Issue submission form can be customized by adding form elements. The "summary", "description", et "reportedby" fields are as default, but any additional qualifier can be added to the issue description.

Elements that can be added are "form elements" i.e. standard form widgets that can represent any qualifier or open description, such as radio buttons, checkboxes, dropdown, textfields or textareas.

Elements are set using the following properties:

### A name

This name is the element identifier, technically speaking. It must be a token using alphanumeric chars and the _ character, without spaces or non printable chars. The name will not appear on the user interface.

### Description

The description is used when the element has to be identified on the user interface.

### Options

Some elements have a finite list of option values.

Options are added after the element is created.

Fieldtexts and textareas do not have any options.
';

$string['options_help'] = '
### A name
The name identifies the option value. It should be a token using alphanumeric chars and _ without spaces or non printable chars.

### Description

The description is the viewable counterpart of the option code.

### Option ordering

You may define the order in which the options appear in the lists.

Textfield and textarea elements do not have any options.';

$string['ticketprefix_help'] = '
This parameter allows defining a fixed prefix thatt will be prepended to the issue numerical identifier. This should allow better identification of a issue entry in documents, forum posts...
';

$string['urgentquery_help'] = '
Checking this checkbox will send a signal to developpers or tickets managers so your issue can be considered more quickly.

Please consider although that there is no automated process using directly this variable. The acceptation of the emergency will be depending on how urgent support administrators have considered your demand.';

$string['mods_help'] = '
This module provides an administrator or technical operator a way to collect locally issues on a Moodle implementation. It may be used mainly as an overall system tool for Moodle administration and support to end users, but also can be used as any other module for student projects. It can be instanciated several times within a course space.
The issue description form is fully customisable. The tracker administrator can add as many description he needs by adding form elements. The integrated search engine do ajust itself to this customization.';

$string['defaultassignee_help'] = '
You might require incoming tickets are preassigned to one of the available resolvers.
';

$string['enablecomemnts_help'] = '
When enabled some roles will be able to comment issues.
';

$string['allownotifications_help'] = '
When enabled some state changes may result in sending notifications to users when user is watching an issue. Users can configure which event will notify them.
';

$string['strictworkflow_help'] = '
When enabled, each specific internal role in tracker (reporter, developer, resolvers, manager) will only have access to his accessible states against his role.
';

$string['networkable_help'] = 'If enabled, this tracker will be openly exposed to remote site. Users from remote site will be able to post even if they have no local account. 
a Mnet account will be created on the fly. This will though only be possible if tracker Mnet services are properly configurated each side.';

$string['failovertrackerurl_help'] = '
Using tracker inside Moodle may not address situation where moodle itself is down or working improperly. When giving a failover tracker url, 
you provide users with an information about an alternate URL they can use in case of major desease. Users will be invited to bookmark the URL in their
own data to get it when needed.
';

$string['failovertrackerurl_tpl'] = '
In case this tracker is not reachable or not available, you may post a signal into the <a href="{$a}">emergency tracker</a>. You should bookmark this URL
to get the link available even if Moodle is down or not operable properly. 
';


// ULPGC strings
$string['mode_tutoring'] = 'Students Tutoring monitor';
$string['mode_usersupport'] = 'User support ULPGC';
$string['mode_boardreview'] = 'Board issue Review';
$string['mode_register'] = 'Register';
$string['message_tutoring'] = 'An entry to support your tutoring plans monitoring and reviewing has been created.';
$string['message_usersupport'] = 'We have registered your query. It will be handled as soon as possible.';
$string['message_boardreview'] = 'Your submission has been registered. Please, wait for review.';
$string['message_register'] = 'Your submission has been registered. Please, wait for review.';
$string['attachment'] = 'Comment attachment';
$string['autoresponse'] = 'Automatic answer';
$string['warninguser'] = 'Administración de Teleformación';
$string['warningemailtxt'] = 'Estimado estudiante:
Se ha creado la incidencia [ {$a->code} ], que le atañe, en la Administración de Teleformación. Puede ver más detalles en el Gestor de incidencias correspondiente
{$a->url}

Este es un mensaje automático. No responda este mensaje, regístrese en Teleformación y visite la Administración.';
$string['warningemailhtml'] = 'Estimado estudiante: <br />
Se ha creado la incidencia [ {$a->code} ], que le atañe, en la Administración de Teleformación. Puede ver más detalles en el <a href="{$a->url}" >Gestor de incidencias</a> correspondiente<br />
<br />
<br />
Este es un mensaje automático. No responda este mensaje, regístrese en Teleformación y visite la Administración.';
$string['warningsubject'] = 'Aviso de la Administración de Teleformación';
$string['sendemail'] = 'Warning by e-mail';
$string['userlastseen'] = 'Viewed by user';
$string['userview'] = 'User issues';
$string['dateupdated'] = 'Change date';
$string['potusers'] = 'Potential users';
$string['potusersmatching'] = 'Potential users matching \'{$a}\'';
$string['showuserissues'] = 'Show user issues';
$string['selectuser'] = 'Select user';
$string['userissues'] = 'Other issues by this user';
$string['dateinterval'] = 'Time interval';
$string['days'] = 'days';
$string['managefiles'] = 'Manage files';
$string['sendtracker'] = 'Tracker instance for bulk user actions';
$string['configsendtracker'] = 'If set, this tracker instance wil be selected automatically by bulk user actions routines when sending a massive issue mailing';
$string['cronruntimestart'] = 'Run at';
$string['configcronruntimestart'] = 'What time should the cronjob that does the priority processing start? Specifying different times is recommended if there are multiple Moodle sites on one server.';
$string['reportmaxfiles'] = 'Reporter N of files';
$string['configreportmaxfiles'] = 'The maximum number of files that can be attached to an issue or comemnt by an user with Report capability.';
$string['developmaxfiles'] = 'Resolver N of files';
$string['configdevelopmaxfiles'] = 'The maximum number of files that can be attached to an issue or comemnt by an user with Develop/Resolve capability.';
$string['required'] = 'Required';
$string['requiredelement'] = 'Required field that cannot be left blank';
$string['lastcomment'] = 'Comments';
$string['closeissue'] = 'Close issue';
$string['closingdays'] = 'Closing days lag';
$string['configclosingdays'] = 'If a value is set then issues in testing state and already seen by users will be closed after these days.';
$string['resolvingdays'] = 'Resolving days';
$string['configresolvingdays'] = 'The number of days allowed to resolve issues.
Controls priority calculations: if user changes issue within this period, priority is reset.
If changed when already delayed, priority is increased.';
$string['description'] = 'Description';
$string['managewords'] = 'Custom words';
$string['wordfor'] = 'Word for "{$a}"';
$string['issueword'] = 'Word for "issue"';
$string['issueword_help'] = 'Any occurrence to the word will be replaced in translated strings with search and replace.
You should define here the word to search and the word to replace, in search:replace format. ';
$string['issueword_explain'] = 'Strings transformed with search & replace. Format search:replace';
$string['issueword'] = 'Word for "assignto"';
$string['summaryword'] = 'Word for "summary"';
$string['descriptionword'] = 'Word for "description"';
$string['statuswords'] = 'Words for status codes';
$string['statuswords_help'] = 'A list of words separated by commas, ordered as in the above line.  <br />

POSTED, <br />
OPEN, <br />
RESOLVING, <br />
WAITING, <br />
RESOLVED, <br />
ABANDONNED, <br />
TRANSFERED, <br />
TESTING, <br />
PUBLISHED, <br />
VALIDATED <br />
';
$string['statuswords_explain'] = 'POSTED, OPEN, RESOLVING, WAITING, RESOLVED, ABANDONNED, TRANSFERED, TESTING, PUBLISHED, VALIDATED';
$string['issuedeleteconfirm'] = 'You are about to delete issue with id {$a} <br />
Are you sure you want to permanently deleet this issue ?';
$string['staffupdated'] = 'Modified by Staff';
$string['lastcomment'] = 'Last comment';
$string['forcedlang'] = 'Forced language';
$string['allopen'] = 'All open';
$string['allclosed'] = 'All closed';
$string['addascced'] = 'Add as watcher';
$string['addasassigned'] = 'Add as working Assignee';
$string['adduserwatch'] = 'Add users as observers in issues where selected.';
$string['adduserwatch_help'] = 'If enabled then the user(s) selected in this menu may be added to the issue attributes either as observer or staff assigned to the issue. <br />
The two options are:  <br />

 * As watcher: user get in the list of issue watches. May add several users in a multi-select menu.
 * As Assignee: user gets the role of assigned staff to workon and resolve the issue
';
$string['autofilltype'] = 'Auto fill options';
$string['autofilltype_help'] = 'Sets the field for autofilling the possible options dynamically. 
The available filling ways are:

 * Courses: Options will be course names in a given course category (defined below).
 * Categories: Options will be course category names within a given parent category (defined below). 
 * Users by role: Options will be the names of users enrolled in the course with a given role (defined below).
 * Users by group: Options will be the names of users enrolled in the course with a given group membership (defined below).
 * Users by grouping: Options will be the names of users enrolled in the course with a given grouping membership (defined below). 

In all cases option keys are the shortname/idnumber values, and displayed names the item display name.
';
$string['autofillusersrole'] = 'Users by role';
$string['autofillusersgroup'] = 'Users by group';
$string['autofillusersgrouping'] = 'Users by grouping';
$string['autofillidnumber'] = 'Auto fill idnumber';
$string['autofillidnumber_help'] = 'If auto filling on the field options is enabled, this parameter defines which items to search to include as options. 
It is readed as the idnumber of the target course category, group or grouping.

You must introduce the idnumber value of the target course category, group or grouping that holds the desired users or courses. 

Leave empty to mean all posible items (users or courses in all groups, groupings or categories)';
$string['autofilltask'] = 'Add options in automatic menu fields';
$string['autowatchestask'] = 'Add watches for selected users in menu';
$string['mycced'] = 'Issues observed';
$string['review'] = 'Summary';

$string['allowsubmissionsfromdate'] = 'Allow new issues from';
$string['allowsubmissionsfromdate_help'] = 'A date to allow non-editing users to add new issues';
$string['duedate'] = 'Due date';
$string['duedate_help'] = 'After this date, no new issues could be aded by non-editing users';
$string['statenonrepeat'] = 'Non-repeat states';
$string['statenonrepeat_help'] = 'If a user has issues in any of the selected stated, the user won\'t be able to create new issues';
$string['reportnotallowed'] = 'New issues cannot be added until the {$a} existing ones be resolved';
$string['reportwillopenon'] = 'Issue creation closed. Will open on: {a}';
$string['reportopenedon'] = 'Issue creation available from: {a}';
$string['reportwillcloseon'] = 'Issue creation will finish on: {$a}';
$string['reportclosedon'] = 'Issue creation was finished on: {$a}';
$string['reportsactive'] = 'You have {$a} active issues in this container';
$string['confirmelementdelete'] = 'About to permanently delete element with name "{$a}".  Are you sure to proceed?';
$string['confirmoptiondelete'] = 'About to permanently delete option with name "{$a}".  Are you sure to proceed?';
$string['closeansweredtask'] = 'Close answered & viewed issues';
$string['updateprioritytask'] = 'Update Tracker issues priority stack';
$string['openstatus'] = 'Open states';
$string['openstatus_desc'] = 'The states that will be considered open, or active, needing action by students or staff.';
