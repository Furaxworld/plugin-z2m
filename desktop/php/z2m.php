<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$z2m_instances = z2m::getDeamonInstanceDef();
sendVarToJS('z2m_instances', $z2m_instances);
// Déclaration des variables obligatoires
$plugin = plugin::byId('z2m');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

foreach ($eqLogics as $eqLogic) {
	$eqLogicArray = array();
	$eqLogicArray['HumanNameFull'] = $eqLogic->getHumanName(true);
	$eqLogicArray['HumanName'] = $eqLogic->getHumanName();
	$eqLogicArray['id'] = $eqLogic->getId();
	$eqLogicArray['instance'] = $eqLogic->getConfiguration('instance', 1);
	$eqLogicArray['img'] = $eqLogic->getImgFilePath();
	$devices[$eqLogic->getLogicalId()] = $eqLogicArray;
	$deviceAttr[$eqLogic->getId()] = array('isgroup' => $eqLogic->getConfiguration('isgroup', 0));
	$deviceAttr[$eqLogic->getId()]['multipleEndpoints'] = $eqLogic->getConfiguration('multipleEndpoints', 0);
	$deviceAttr[$eqLogic->getId()]['isChild'] = $eqLogic->getConfiguration('isChild', 0);
}
$devices[0] = array('HumanNameFull' => 'Contrôleur', 'HumanName' => 'Contrôleur', 'id' => 0, 'img' => 'plugins/z2m/core/config/devices/coordinator.png');
sendVarToJS('z2m_devices', $devices);
sendVarToJS('devices_attr', $deviceAttr);
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
		<div class="eqLogicThumbnailContainer">
			<div class="cursor changeIncludeState include card logoPrimary" data-mode="1" data-state="1">
				<i class="fas fa-sign-in-alt fa-rotate-90"></i>
				<br />
				<span>{{Mode inclusion}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_includeDeviceByCode">
				<i class="fas fa-sign-in-alt fa-rotate-90"></i>
				<br />
				<span>{{Inclusion par code}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_z2mNetwork">
				<i class="fas fa-sitemap"></i>
				<br>
				<span>{{Réseaux Zigbee}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_addGroup">
				<i class="fas fa-object-group"></i>
				<br>
				<span>{{Ajouter un groupe}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes modules Zigbee}}</legend>
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>
				<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('isgroup', 0) != 0) {
					continue;
				}
				$child = ($eqLogic->getConfiguration('isChild', 0) == 1) ? '<i style="position:absolute;font-size:1.5rem!important;right:10px;top:10px;" class="icon_orange fas fa-user" title="Ce device est un enfant"></i>' : '';
				$child .= ($eqLogic->getConfiguration('multipleEndpoints', 0) == 1 && $eqLogic->getConfiguration('ischild', 0) == 0) ? '<i style="position:absolute;font-size:1.5rem!important;right:10px;top:10px;" class="icon_green fas fa-random" title="Ce device peut être séparé en enfants"></i>' : '';
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';
				echo '<img src="' . $eqLogic->getImgFilePath() . '" />'. $child;
				echo "<br/>";
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
		<legend><i class="fas fa-object-group"></i> {{Mes groupes Zigbee}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			$child = '<i style="position:absolute;font-size:1.5rem!important;right:10px;top:10px;" class="icon_green fas fa-object-group" title="Groupe"></i>';
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('isgroup', 0) == 1) {
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';

					echo '<img src="' . $plugin->getPathImgIcon() . '" />' . $child;

					echo "<br/>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a id="bt_childCreate" class="btn btn-success btn-sm childCreate" style="display : none;"><i class="fas fa-user"></i> {{Créer un enfant}}
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux et spécifiques de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Paramètres spécifiques}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Identification}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Identifiant du module}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="Logical ID" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Contrôleur z2m}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Sélectionner le contrôleur en communication avec ce module}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="instance">
										<?php
										foreach ($z2m_instances as $z2m_instance) {
											if ($z2m_instance['enable'] != 1) {
												continue;
											}
											echo '<option value="' . $z2m_instance['id'] . '">' . $z2m_instance['name'] . '</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Model}}</label>
								<div class="col-sm-7">
									<span class="eqLogicAttr label label-info" data-l1key="configuration" data-l2key="device" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"></label>
								<div class="col-sm-7">
									<div id="div_instruction"></div>
									<div style="height:220px;display:flex;justify-content:center;align-items:center;">
										<img src="plugins/z2m/plugin_info/z2m_icon.png" data-original=".jpg" id="img_device" class="img-responsive" style="max-height:200px;max-width:200px;" onerror="this.src='plugins/z2m/plugin_info/z2m_icon.png'" />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"></label>
								<div class="col-sm-7">
									<a id="bt_showZ2mDevice" class="btn btn-primary"><i class="fas fa-wrench"></i> {{Configuration du module}}</a>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
				<br><br>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th style="width: 450px;">{{Nom}}</th>
								<th style="width: 130px;">{{Type}}</th>
								<th>{{Logical ID}}</th>
								<th>{{Paramètres}}</th>
								<th style="width:300px;">{{Options}}</th>
								<th>{{Etat}}</th>
								<th style="width: 150px;">{{Action}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #commandtab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<?php include_file('desktop', 'z2m', 'js', 'z2m'); ?>
<?php include_file('core', 'z2m', 'class.js', 'z2m'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>