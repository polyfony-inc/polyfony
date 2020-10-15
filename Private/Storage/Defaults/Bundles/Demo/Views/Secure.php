<?php use Polyfony\Config as Config; ?>

<div class="jumbotron" style="padding:45px;">
	
	<h1>Security</h1>
	
	<p>
		If you see this, <strong>you are authenticated !</strong> Bellow are your credentials
	</p>
	<p>
		Account <strong>id</strong> is <code><?= $id; ?></code>, <br />
		Accounts <strong>login</strong> is <code><?= $login; ?></code>, <br />
		Account <strong>roles</strong> are 

			<?php foreach($roles as $role): ?>
				<?= $role->getBadge(); ?>
			<?php endforeach; ?>

		<br />
		Account <strong>permissions</strong> are 
		
			<?php foreach($permissions as $permission): ?>
				<?= $permission->getBadge(); ?>
			<?php endforeach; ?>
		 <br />
	</p>
	
	<p>
		To close your session, <a href="/demo/disconnect/">click here</a>
	</p>

</div>
<div class="container">
	<div class="row">
		<div class="col-12">
			<h2>
				List of accounts
			</h2>
			<table class="table table-hover table-striped">
				<thead>
					<tr>
						<th>
							Login
						</th>
						<th>
							Expiration
						</th>
						<th>
							Roles
						</th>
						<th>
							Permissions
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach(
						$accounts as 
						$account
					): ?>
						<tr>
							<td>
								<?= $account->get('login'); ?>
							</td>
							<td>

							</td>
							<td>
								<?= $account->getRolesBadges(); ?>
							</td>
							<td>
								<?= $account->getPermissionsBadges(); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="col-6">
			<h3>
				List of roles
			</h3>
		</div>
		<div class="col-6">
			<h3>
				List of permissions
			</h3>
		</div>
	</div>
</div>