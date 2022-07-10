<?php

$subscriptions = $_["subscriptions"];
$subStats = $_["subStats"];

?>

<div class="section">
	<h2>Synced subscriptions (<?= count($subscriptions) ?>)</h2>

	<?php if (count($subscriptions) == 0) { ?>
	<div>
		Your account has no subscriptions (podcasts) synced.
	</div>
	<?php } else { ?>
	<ul style="list-style: disc; margin-left: 1em; padding-left: 1em;">
		<?php foreach ($subscriptions as $sub) { ?>
		<li>
			<?= $sub ?>
			<dl>
				<dt>Episodes started:</dt>
				<dd><?= $subStats[$sub] ? $subStats[$sub]["started"] : 0 ?></dd>
			</dl>
		</li>
		<?php } ?>
	</ul>
	<?php } ?>
</div>
