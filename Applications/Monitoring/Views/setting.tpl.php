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
			<ul class="breadcrumb">
				<li>
					<a href="/?fn=setting">Settings</a> <span class="divider">/</span>
				</li>
				<li class="active">
					Option List
				</li>
			</ul>
			<?php if($suc_msg){?>
				<div class="alert alert-dismissable alert-success">
				 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				 <strong><?php echo $suc_msg;?></strong> 
				</div>
			<?php }elseif($err_msg){?>
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
		</div>
	</div>
	<div class="row clearfix">
		<div class="col-md-3 column">
		</div>
		<div class="col-md-6 column">
			<form class="form-horizontal" role="form" action="/?fn=setting&act=save" method="post">
				<div class="form-group">
					 <label class="col-sm-3 control-label">Data Source DetectionPort</label>
					<div class="col-sm-9">
						<input class="form-control" name="detect_port" value="<?php echo $detect_port;?>"/>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						 <button type="submit" class="btn btn-default">Save</button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-3 column">
		</div>
	</div>
</div>
