<?php 
use Polyfony\Locales as Locales;
use Polyfony\Format as Format;
use Polyfony\Request as Request;
use Polyfony\Config as Config;
use Polyfony\Security as Security;
use Polyfony\Exception as Exception;
?>
<style type="text/css">
	.row {
		padding-top: 2em;
	}
	.card {
		margin-top: 20px; 
		display: none;
		line-height: 24px;
	}
	code strong {
		font-weight: bold;
	}
	#trace, 
	#dump {
		display: none;
	}
	.buttons {
		margin-top: 20px;
	}
	.card {
		font-family: monospace;
		font-size: 14px;
	}
</style>
<div class="container-fluid">
	
	<div class="row justify-content-center" >
		
		<div class="col-12 col-sm-12 col-md-10 col-lg-8">	
			
			<h1>
				
				<span class="<?= $icon; ?>"></span> 
				<?= Locales::get($message); ?>
			
			</h1>

			<?php if($requires_attention): ?>
		
			<code>
				<strong>
					@line <?= $line; ?>
				</strong> 
				in <?= $file; ?> 
			</code>

			<div class="buttons">

				<div class="btn-group">
					
					<a 
					href="#report-incident" 
					class="btn btn-warning" 
					onclick="reportIncident()">
					
						<span class="fa fa-envelope"></span> 
						<?= Locales::get('Send_event_to_tech_support'); ?> 
					
					</a> 

					<a 
					href="#techical-details" 
					class="btn btn-light" 
					onclick="$('#trace').toggle(250);">
					
						<span class="fa fa-caret-down"></span> 
						<?= Locales::get('Technical_details'); ?> 

					</a>

				</div>

			</div>

			<div 
			class="card" 
			id="trace">
				<?= $html_trace; ?>
			</div>

<textarea id="dump">
Error : <?= $message; ?>

Code : <?= $code; ?>

Date : <?= date('r'); ?> 
URL : <?= Format::htmlSafe(Request::getUrl()); ?>

Method : <?= Request::isPost() ? 'POST' : 'GET'; ?>

REFERER : <?= Format::htmlSafe(Request::server('HTTP_REFERER', 'None')); ?>

Domain : <?= Config::get('router','domain'); ?>

Agent : <?= Format::htmlSafe(Request::server('HTTP_USER_AGENT')); ?>

IP : <?= Format::htmlSafe(Request::server('REMOTE_ADDR')); ?>

Protocol : <?= Request::getProtocol(); ?>

User : <?= !Security::isAuthenticated() ?: Security::getAccount()->get('login'); ?>

Trace : 
<?= $string_trace; ?>
</textarea>
<?php endif; ?>
<script type="text/javascript">
function reportIncident() {

	var link = "mailto:"+'<?= Config::get('mail','tech_support_mail'); ?>'
	+ "?subject=" + escape("Exception (<?= Config::get('router','domain'); ?>) - <?= $message; ?>")
	+ "&body=" + escape(document.getElementById('dump').value);

	window.location.href = link;

}
</script>
		</div>
	</div>
</div>
