<?php only_admin_access();?>

<div class="module-live-edit-settings">

<style>
	.mw-mod-template-settings-holder {float:right}
	.mw-mod-template-settings-holder label, .mw-mod-template-settings-holder select { float:left}
	.mw-mod-template-settings-holder label {margin-top:11px;margin-right:10px}
</style>
<script>
d(window)

 mw.lib.require('jqueryui');
 </script>

<script>
	 //mw.lib.require('jqueryui'); // hyphen removed due to mw bug
     mw.require("<?php print $config['url_to_module'];?>fullcalendar-3.1.0/fullcalendar.min.css");
     mw.require("<?php print $config['url_to_module'];?>fullcalendar-3.1.0/lib/moment.min.js");
     mw.require("<?php print $config['url_to_module'];?>fullcalendar-3.1.0/fullcalendar.min.js");
</script>
<style>
	#newevent{ cursor: move;width:80px;text-align:center;margin-top:10px}
	#external-events {margin-bottom:10px}
	#trash {float:left;width:80px;text-align:center}
	#eventContent {display:table;width:100%!important;}
	#eventContent .row { width:100%;clear:both;padding-bottom:10px;}
	#eventContent .row label {width:80px;vertical-align:top;float:left;display:table-column;}
	#eventContent .row col {width:auto;float:left;display:table-column;}
	#eventContent .row .colElement {width:220px;}
	.ui-dialog-buttonpane .leftButton {float:left}
</style>
 <script>
	$(document).ready(function() {

		var zone = '<?php echo date('P');?>';

		var currentMousePos = {
			x: -1,
			y: -1
		};

		jQuery(document).on("mousemove", function (event) {
			currentMousePos.x = event.pageX;
			currentMousePos.y = event.pageY;
		});

		/* initialize the external events
		-----------------------------------------------------------------*/

		$('#external-events .fc-event').each(function() {

			// store data so the calendar knows to render an event upon drop
			$(this).data('event', {
				title: $.trim($(this).text()), // use the element's text as the event title
				stick: true // maintain when user navigates (see docs on the renderEvent method)
			});

			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});

		});

		/* initialize the calendar
		-----------------------------------------------------------------*/

		$('#calendar').fullCalendar({
			//events for selected month are loaded in viewRender event
			//events: JSON.parse(json_events),
			// test event
			//events: [{"id":"14","title":"New Event","start":"2017-01-17T16:00:00+04:00","allDay":false}],
			utc: true,
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listWeek'
			},
			editable: true,
			droppable: true,
			slotDuration: '00:30:00',

			eventReceive: function(event){
				var title = event.title;
				var start = event.start.format("YYYY-MM-DD[T]HH:mm:SS");
				console.log('eventReceive start: '+start);
				$.ajax({
		    		url: '<?php print api_url('new_event');?>',
		    		data: 'title='+title+'&startdate='+start+'&zone='+zone,
		    		type: 'POST',
		    		dataType: 'json',
		    		success: function(response){
		    			event.id = response.eventid;
		    			$('#calendar').fullCalendar('updateEvent',event);

					  	reload_calendar_after_save();


		    		},
		    		error: function(e){
		    			console.log(e.responseText);

		    		}
		    	});
				$('#calendar').fullCalendar('updateEvent',event);
				console.log(event);
			},

			eventDrop: function(event, delta, revertFunc) {
		        var title = event.title;
		        var start = event.start.format();
		        var end = (event.end == null) ? start : event.end.format();
		        $.ajax({
					url: '<?php print api_url('reset_date');?>',
					data: 'title='+title+'&start='+start+'&end='+end+'&eventid='+event.id,
					type: 'POST',
					dataType: 'json',
					success: function(response){
						if(response.status == 'success') $('#calendar').fullCalendar('updateEvent',event);
						if(response.status != 'success') revertFunc();

					 	reload_calendar_after_save();
					},
					error: function(e){
						revertFunc();
						alert('Error processing your request: '+e.responseText);
					}
				});
		    },

		    eventClick: function(event, jsEvent, view) {
		    	console.log(event.id);
		        //var title = prompt('Event Title:', event.title, { buttons: { Ok: true, Cancel: false} });
				$("#title").val(event.title);
				$("#description").val(event.description);

				/*
				if(event.allDay == true) {
					$("#eventContent .time").hide();
					$("#allDay").prop( "checked", true );
				} else {
					$("#eventContent .time").show();
				}
				*/
				var startDate = moment(event.start).format("YYYY-MM-DD");
				var startTime = moment(event.start).format('hh:mm a');
				if(startTime == "12:00 am") startTime = "00:00 am";
				$("#starttime").val(startTime);
				if(event.end != null) {
					var endDate = moment(event.end).format("YYYY-MM-DD");
					var endTime = moment(event.end).format('hh:mm a');
					$("#endtime").val(endTime);
				}

				$("#eventContent").dialog({ modal: true,
											resizable: false,
											title: event.title,
											width:350,
											buttons: {
												"Cancel": function() {
													$(this).dialog("close");
												},
												"Save": function() {
													event.title = $("#title").val();
													event.description = $("#description").val();
													event.start = moment(startDate+' '+$("#starttime").val()).format("YYYY-MM-DD[T]HH:mm:SS");
													event.end = moment((event.end!=null?endDate:startDate)+' '+$("#endtime").val()).format("YYYY-MM-DD[T]HH:mm:SS");

													console.log('function=change_title&title='+event.title+'&description='+event.description+'&eventid='+event.id+'&start='+event.start+'&end='+event.end);
													$.ajax({
															url: '<?php print api_url('change_title');?>',
															data: 'title='+event.title+'&description='+event.description+'&eventid='+event.id+'&start='+event.start+'&end='+event.end+'&zone='+zone,
															type: 'POST',
															dataType: 'json',
															success: function(response){
																if(response.status == 'success')
																	$('#calendar').fullCalendar('updateEvent',event);
																//	reload_calendar_after_save();

															},
															error: function(e){
																alert('Error processing your request: '+e.responseText);
															}
													});
													$(this).dialog("close");
												},
												"Delete":{
													class: 'leftButton',
													text: 'Delete',
													click : function (){
														deleteEvent(event);
														$(this).dialog("close");
													}
												}
											}
    			});

			},

			eventResize: function(event, delta, revertFunc) {
				//console.log(event);
				var title = event.title;
				var end = event.end.format();
				var start = event.start.format();
				//console.log('end: '+end);
		        $.ajax({
					url: '<?php print api_url('reset_date');?>',
					data: 'title='+title+'&start='+start+'&end='+end+'&eventid='+event.id+'&zone='+zone,
					type: 'POST',
					dataType: 'json',
					success: function(response){
						if(response.status == 'success') $('#calendar').fullCalendar('updateEvent',event);

					//	reload_calendar_after_save();


						//if(response.status != 'success') revertFunc();
					},
					error: function(e){
						revertFunc();
						alert('Error processing your request: '+e.responseText);
					}
				});
		    },

			eventDragStop: function (event, jsEvent, ui, view) {
			    if (isElemOverDiv()) {
					deleteEvent(event);
				}
			},

			viewRender: function (view, element) {
					// getData for selected year-month
					getData();
					$('#calendar').fullCalendar('removeEvents');
					$('#calendar').fullCalendar('addEventSource',JSON.parse(json_events));
 			},

			eventMouseover: function(event, element) {
				tooltip = '<div class="tooltipevent" style="width:auto;height:auto;background:#eee;position:absolute;z-index:10001;padding:10px 10px 10px 10px;line-height: 150%;">' + event.title + '</br>' + 'date: ' + moment(event.start).format('Do MMM') + '</br>' + 'from: ' + moment(event.start).format('h:mm a') + (event.end == null?'':'</br>' + 'to: ' + moment(event.end).format('h:mm a'))  + (event.description == null?'':'</br>' + event.description) + '</div>';

				$("body").append(tooltip);
				$(this).mouseover(function (e) {
					$(this).css('z-index', 10000);
					$('.tooltipevent').fadeIn('500');
					$('.tooltipevent').fadeTo('10', 1.9);
				}).mousemove(function (e) {
					$('.tooltipevent').css('top', e.pageY + 10);
					$('.tooltipevent').css('left', e.pageX + 20);
				});
      		},
			eventMouseout: function (data, event, view) {
				$(this).css('z-index', 8);
				$('.tooltipevent').remove();
			},
			dayClick: function () {
				$('.tooltipevent').remove();
			},
			eventResizeStart: function () {
				$('.tooltipevent').remove();
			},
			eventDragStart: function () {
				$('.tooltipevent').remove();
			},
			viewDisplay: function () {
				$('.tooltipevent').remove();
        	}

		});

		function deleteEvent(event){
			var con = confirm('Are you sure to delete this event permanently?');
			if(con == true) {
				$.ajax({
					url: '<?php print api_url('remove_event');?>',
					data: 'eventid='+event.id,
					type: 'POST',
					dataType: 'json',
					success: function(response){
						console.log(response);
						if(response.status == 'success'){
							$('#calendar').fullCalendar('removeEvents',event.id);  // not reliable
							getData();
							$('#calendar').fullCalendar('removeEvents');
							$('#calendar').fullCalendar('addEventSource',JSON.parse(json_events));
							//getFreshEvents();
							reload_calendar_after_save();
						}
					},
					error: function(e){
						alert('Error processing your request: '+e.responseText);
					}
				});
			}
		}

		function getData(yearmonth) {
			// getData for selected year-month
			var date = $("#calendar").fullCalendar('getDate');
			var y = date.year();
			var m = ("0" + (date.month() + 1)).slice(-2);
			var yearmonth = y+'-'+m;
			$.ajax({
				url: '<?php print api_url('get_events');?>',
				type: 'POST', // Send post data
				data: 'yearmonth='+yearmonth,
				async: false,
				success: function(s){
					json_events = s;
				}
			});
		}

		function isElemOverDiv() {
			var trashEl = jQuery('#trash');

			var ofs = trashEl.offset();

			var x1 = ofs.left;
			var x2 = ofs.left + trashEl.outerWidth(true);
			var y1 = ofs.top;
			var y2 = ofs.top + trashEl.outerHeight(true);

			if (currentMousePos.x >= x1 && currentMousePos.x <= x2 &&
				currentMousePos.y >= y1 && currentMousePos.y <= y2) {
				return true;
			}
			return false;
		}

		$('#newevent').tooltip();
		$('#trash').tooltip();

	});



function reload_calendar_after_save(){
 	//mw.reload_module_parent('#<?php print $params['id'] ?>');
	window.parent.$(window.parent.document).trigger('calendar.update');

}
</script>


<?php
$time_options = '';
$range=range(strtotime("00:00"),strtotime("23:30"),30*60);
foreach($range as $time){
	$time_options .=  '<option value="' . date("H:i",$time) . '">' . date("h:i a",$time) . "</option>\n";
}
?>
<div id="eventContent" title="Event Details" style="display:none;">
	<div class="row" style="margin-top:10px;">
      <label for="title">Event Title:</label>
      <div class="col"><input id="title" class="colElement" type="text" /></div>
    </div>
    <div class="row">
      <label for="description">Description:</label>
      <div class="col"><textarea id="description" class="colElement" rows="4" cols="20"> </textarea></div>
    </div>
	<?php /*
	<div class="row allDay">
		<label for="allDay">All day:</label><input type="checkbox" id="allDay" name="allDay" value="1">
	</div>
	*/ ?>
    <div class="row time">
		<label for="starttime">Start Time:</label>
		<div class="col"><select id="starttime"><?php print $time_options;?></select></div>
    </div>
    <div class="row time">
		<label for="endtime">End Time:</label>
		<div class="col"><select id="endtime"><?php print $time_options;?></select></div>
    </div>
</div>

<div id="external-events">
	<p id="trash"><img  data-toggle="tooltip" data-placement="auto" title="Remove event by dragging to dustbin" src="<?php print $config['url_to_module'];?>trashcan.png" alt=""></p>
	<div style="float:left">
		<div id="newevent" draggable="true" data-toggle="tooltip" data-placement="right" class="fc-event" title="Create new event by dragging to calendar">New Event</div>
	</div>
	<module type="admin/modules/templates" simple=true  />
	<div style='clear:both'></div>
</div>

<div id='calendar'></div>
</div>
