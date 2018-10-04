<div class="container">
	<div class="row clearfix">
		<div class="col-md-12 column">
			<ul class="nav nav-tabs">
				<li>
					<a href="/">Overview</a>
				</li>
				<li>
					<a href="/?fn=statistic">Monitor</a>
				</li>
				<li class="active" >
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
		<?php echo $log_str;?>
		</div>
	</div>
</div>
