<div class="container">
	<div class="row clearfix">
		<div class="col-md-12 column">
			<ul class="nav nav-tabs">
				<li>
					<a href="/">Overview</a>
				</li>
				<li class="active" >
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
		<div class="col-md-3 column">
			<ul><?php echo $module_str;?></ul>
		</div>
		<div class="col-md-9 column">
		<?php if($err_msg){?>
			<div class="alert alert-dismissable alert-danger">
				 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<strong><?php echo $err_msg;?></strong> 
			</div>
		<?php }?>
		<?php if($module && $interface){?>
			<div class="row clearfix">
				<div class="col-md-12 column text-center">
					<?php echo $date_btn_str;?>
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
			<?php if($module && $interface){?>
			<script>
			Highcharts.setOptions({
				global: {
					useUTC: false
				}
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
							text: 'Request volume (times/5 minutes)'
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
					series: [{
						name: 'Success curve',
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
						name: 'Failure curve',
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
							text: 'Average time (in seconds)'
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
					series: [{
						name: 'Success curve',
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
						name: 'Failure curve',
						data: [
								<?php echo $fail_time_series_data;?>
						],
						lineWidth: 2,
						marker:{
							radius: 1
						},
						pointInterval: 300*1000,
						color : '#9C0D0D'
					}]
				});
			</script>
			<?php }?>
			<table class="table table-hover table-condensed table-bordered">
				<thead>
					<tr>
						<th>Time</th><th>Total number of calls</th><th>Average Time</th><th>SuccessTotal number of calls</th><th>SuccessAverage Time</th><th>otal number of Failed calls</th><th>Average Time Failed</th><th>Success rate</th>
					</tr>
				</thead>
				<tbody>
				<?php echo $table_data;?>
				</tbody>
			</table>
			<?php }?>
		</div>
	</div>
</div>
