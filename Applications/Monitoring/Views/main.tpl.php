<div class="container">
	<div class="row clearfix">
		<div class="col-md-12 column">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="/">Overview</a>
				</li>
				<li>
					<a href="/?fn=statistic">Monitor</a>
				</li>
				<li>
					<a href="/?fn=logger">Log</a>
				</li>
				<li class="disabled">
					<a href="#">Alarm</a>
				</li>
				<li class="dropdown pull-right">
					 <a href="#" data-toggle="dropdown" class="dropdown-toggle">Other<strong class="caret"></strong></a>
					<ul class="dropdown-menu">
						<li>
							<a href="/?fn=admin&act=detect_server">Probe Data Source</a>
						</li>
						<li>
							<a href="/?fn=admin">Data Source management</a>
						</li>
						<li>
							<a href="/?fn=setting">Settings</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<div class="row clearfix">
		<div class="col-md-12 column">
			<?php if($err_msg){?>
			<div class="alert alert-dismissable alert-danger">
				 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<strong><?php echo $err_msg;?></strong> 
			</div>
			<?php }elseif($notice_msg){?>
			<div class="alert alert-dismissable alert-info">
				 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<strong><?php echo $notice_msg;?></strong>
			</div>
			<?php }?>
			<div class="row clearfix">
				<div class="col-md-12 column text-center">
					<?php echo $date_btn_str;?>
				</div>
			</div>
			<div class="row clearfix">
				<div class="col-md-6 column height-400" id="suc-pie">
				</div>
				<div class="col-md-6 column height-400" id="code-pie">
				</div>
			</div>
			<div class="row clearfix">
				<div class="col-md-12 column height-400" id="req-container" >
				</div>
			</div>
			<div class="row clearfix">
				<div class="col-md-12 column height-400" id="time-container" >
				</div>
			</div>
			<script>
Highcharts.setOptions({
	global: {
		useUTC: false
	}
});
	$('#suc-pie').highcharts({
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false
		},
		title: {
			text: '<?php echo $date;?> Availability'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					color: '#000000',
					connectorColor: '#000000',
					format: '<b>{point.name}</b>: {point.percentage:.1f} %'
				}
			}
		},
		credits: {
			enabled: false,
		},
		series: [{
			type: 'pie',
			name: 'Availability',
			data: [
				{
					name: 'Available',
					y: <?php echo $global_rate;?>,
					sliced: true,
					selected: true,
					color: '#2f7ed8'
				},
				{
					name: 'Unavailable',
					y: <?php echo (100-$global_rate);?>,
					sliced: true,
					selected: true,
					color: '#910000'
				}
			]
		}]
	});
	$('#code-pie').highcharts({
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false
		},
		title: {
			text: '<?php echo $date;?> Return Code Distribution'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					color: '#000000',
					connectorColor: '#000000',
					format: '<b>{point.name}</b>: {point.percentage:.1f} %'
				}
			}
		},
		credits: {
			enabled: false,
		},
		series: [{
			type: 'pie',
			name: 'Return Code Distribution',
			data: [
				<?php echo $code_pie_data;?>
			]
		}]
	});
	$('#req-container').highcharts({
		chart: {
			type: 'spline'
		},
		title: {
			text: '<?php echo "$date $interface_name";?>  Request curve'
		},
		subtitle: {
			text: ''
		},
		xAxis: {
			type: 'datetime',
			dateTimeLabelFormats: { 
				hour: '%H:%M'
			}
		},
		yAxis: {
			title: {
				text: 'Request Volume(Times/5minute)'
			},
			min: 0
		},
		tooltip: {
			formatter: function() {
				return '<p style="color:'+this.series.color+';font-weight:bold;">'
				 + this.series.name + 
				 '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">Time：' + Highcharts.dateFormat('month %m day %d %H:%M', this.x) +
				 '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">Quantity：'+ this.y + '</p>';
			}
		},
		credits: {
			enabled: false,
		},
		series: [		{
			name: 'Success Curve',
			data: [
				<?php echo $success_series_data;?>
			],
			lineWidth: 2,
			marker:{
				radius: 1
			},
			
			pointInterval: 300*1000
		},
		{
			name: 'Failure Curve',
			data: [
				<?php echo $fail_series_data;?>
			],
			lineWidth: 2,
			marker:{
				radius: 1
			},
			pointInterval: 300*1000,
			color : '#9C0D0D'
		}]
	});
	$('#time-container').highcharts({
		chart: {
			type: 'spline'
		},
		title: {
			text: '<?php echo "$date $interface_name";?>  Request time-consuming curve'
		},
		subtitle: {
			text: ''
		},
		xAxis: {
			type: 'datetime',
			dateTimeLabelFormats: { 
				hour: '%H:%M'
			}
		},
		yAxis: {
			title: {
				text: 'Average Time(Unit：second)'
			},
			min: 0
		},
		tooltip: {
			formatter: function() {
				return '<p style="color:'+this.series.color+';font-weight:bold;">'
				 + this.series.name + 
				 '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">Time：' + Highcharts.dateFormat('month %m day %d %H:%M', this.x) +
				 '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">Average Time：'+ this.y + '</p>';
			}
		},
		credits: {
			enabled: false,
		},
		series: [		{
			name: 'Success Curve',
			data: [
				<?php echo $success_time_series_data;?>
			],
			lineWidth: 2,
			marker:{
				radius: 1
			},
			pointInterval: 300*1000
		},
		{
			name: 'Failure Curve',
			data: [
				   <?php echo $fail_time_series_data;?>
			],
			lineWidth: 2,
			marker:{
				radius: 1
			},
			pointInterval: 300*1000,
			color : '#9C0D0D'
		}			]
	});
</script>
			<table class="table table-hover table-condensed table-bordered">
				<thead>
					<tr>
						<th>Time</th><th>Total number of calls</th><th>Average Time</th><th>SuccessTotal number of calls</th><th>SuccessAverage Time</th><th>Total number of Failed calls</th><th>Average Time Failed</th><th>Success rate</th>
					</tr>
				</thead>
				<tbody>
				<?php echo $table_data;?>
				</tbody>
			</table>
		</div>
	</div>
</div>
