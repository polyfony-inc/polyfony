<?php use Polyfony as pf; ?>
<style type="text/css">
	.row {
		padding-top: 2em;
	}
	.card {
		margin-top: 20px; 
		display: none;
	}
	.card-body {
		padding-bottom: 0;
	}
</style>
<div class="container-fluid">
	<div class="row justify-content-center" >
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">	
			<h1>
				<span class="<?= $this->icon; ?>"></span> 
				<?php echo pf\Locales::get($this->Exception->getMessage()); ?>
			</h1>
			<div class="">
				<a href="#" class="btn btn-warning" onclick="reportIncident()">
					<span class="fa fa-send"></span> 
					<?php echo pf\Locales::get('Envoyer cet incident à l\'équipe technique'); ?> 
				</a> 
				<a href="#" class="btn btn-outline-secondary" onclick="document.getElementById('trace').style.display='block';">
					<?php echo pf\Locales::get('Détails techniques'); ?> 
					<span class="fa fa-caret-down"></span>
				</a>
			</div>
			<div class="card" id="trace">
				<div class="card-body">
					<?php echo $this->Trace ?: null; ?>
				</div>
			</div>
<textarea id="dump" style="display:none;">
Date : <?php echo date('r'); ?> 
URL : <?php echo pf\Request::getUrl(); ?>

REFERER : <?php echo pf\Request::server('HTTP_REFERER'); ?>

Domain : <?php echo pf\Config::get('router','domain'); ?>

Agent : <?php echo pf\Request::server('HTTP_USER_AGENT'); ?>

Method : <?php echo pf\Request::isPost() ? 'POST' : 'GET'; ?>

IP : <?php echo pf\Request::server('REMOTE_ADDR'); ?>

Protocol : <?php echo pf\Request::getProtocol(); ?>

User : <?php echo pf\Security::get('id'); ?>

Error : <?php echo $this->Exception->getCode(); ?> <?php echo $this->Exception->getMessage(); ?>

Trace : 
<?php echo $this->Exception->getTraceAsString(); ?>
</textarea>
<script type="text/javascript">
function reportIncident() {

	var tech_support_mail = '<?php echo pf\Config::get('mail','tech_support_mail'); ?>';

	var link = "mailto:"+tech_support_mail
	+ "?subject=" + escape("Exception (<?php echo pf\Config::get('router','domain'); ?>)")
	+ "&body=" + escape(document.getElementById('dump').value);

	window.location.href = link;

}
</script>
		</div>
	</div>
</div>
