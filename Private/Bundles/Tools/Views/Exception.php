<?php use Polyfony as pf; ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">	

			<h1>
				<span class="glyphicon glyphicon-exclamation-sign"></span> 
				<?php echo pf\Locales::get($this->Exception->getMessage()); ?>
			</h1>
			<div class="">
				<a href="#" class="btn btn-warning" onclick="reportIncident()">
					<?php echo pf\Locales::get('Envoyer cet incident à l\'équipe technique'); ?> 
				</a> 
				<a href="#" class="btn btn-default" onclick="document.getElementById('trace').style.display='block';">
					<?php echo pf\Locales::get('Détails techniques'); ?> 
					<span class="glyphicon glyphicon-menu-down"></span>
				</a>
			</div>
			<div class="" style="clear:both;padding-top:20px;display:none;" id="trace">
				<?php echo $this->Trace ?: null; ?>
			</div>
<textarea id="dump" style="display:none;">
Date : <?php echo date('r'); ?> 
URL : <?php echo pf\Request::getUrl(); ?>

REFERER : <?php echo pf\Request::server('HTTP_REFERER'); ?>

Domain : <?php echo pf\Config::get('router','domain'); ?>

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