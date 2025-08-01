<?php

/** @var \App\Support\PaginatedResult<\App\Http\Intervention\DTOs\InterventionDto> $paginatedResults */
/** @var \App\Http\User\DTOs\UserStaffDto[] $udiStaff */
/** @var \App\Http\User\DTOs\UserDto[] $users */
/** @var \App\Http\Keyword\DTOs\KeywordDto[] $keywords */
/** @var \App\Http\Intervention\DTOs\InterventionTypeDto[] $interventionTypes */
/** @var \App\Http\Material\DTOs\MaterialDto[] $materials */

use App\Http\Intervention\InterventionState;
?>

<?php $this->extend('layout') ?>

<?php $this->section('title') ?>Liste des interventions<?php $this->endSection() ?>

<?php $this->section('style') ?>
<link rel="stylesheet" href="./styles/intervention/index.css">
<?php $this->endSection() ?>

<?php $this->section('script') ?>
<script src="./scripts/intervention/index.js" type="module"></script>
<?php $this->endSection() ?>


<?php $this->section('content') ?>
<div class="intervention_root">
   <div class="intervention_container">
      <div class="table_container">
         <table>
            <thead>
               <tr>
                  <th>Id</th>
                  <th>Date</th>
                  <th>Personne</th>
                  <th>Service</th>
                  <th>Intervenants</th>
                  <th>Sujet</th>
                  <th>Titre</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $interventions = $paginatedResults->data;
               foreach ($interventions as $intervention):
               ?>
                  <tr class="intervention_row" data-intervention-id="<?= $this->escape($intervention->id) ?>">
                     <td><b><?= $intervention->id ?></b></td>
                     <td>
                        <?php
                        $datetime = DateTime::createFromFormat("Y-m-d H:i:s", $intervention->requestDate);
                        $date = $this->escape($datetime->format("d/m/Y"));
                        $time = $this->escape($datetime->format("H:i"));
                        echo "<div><b>$date</b></div>";
                        echo "<div><span>$time</span></div>";
                        ?>

                     </td>
                     <td><?= $this->escape($intervention->targetUserName) ?></td>
                     <td>
                        <?php foreach ($intervention->services as $service): ?>
                           <div><span><?= $this->escape($service->name) ?></span></div>
                        <?php endforeach ?>
                     </td>
                     <td>
                        <?php if ($intervention->helpers == []) echo "-" ?>
                        <?php foreach ($intervention->helpers as $helper): ?>
                           <div><span><?= $this->escape($this->truncate($helper->surname, 4, "")) ?></span></div>
                        <?php endforeach ?>
                     </td>
                     <td><?= $this->escape($intervention->subtypeName ?? "-") ?></td>
                     <td><?= $this->escape($this->truncate($intervention->title ?? "-", 30)) ?></td>
                     <td>

                        <?php if ($intervention->status == InterventionState::RECEIVED): ?>

                           <b class="text_info"><?= $this->escape($intervention->status) ?></b>

                        <?php elseif ($intervention->status == InterventionState::CLOSED): ?>

                           <b class="text_danger"><?= $this->escape($intervention->status) ?></b>

                        <?php elseif ($intervention->status == InterventionState::PERSISTENT): ?>

                           <b class="text_warning"><?= $this->escape($intervention->status) ?></b>

                        <?php elseif ($intervention->status == InterventionState::IN_PROGRESS): ?>

                           <b class="text_success"><?= $this->escape($intervention->status) ?></b>

                        <?php endif; ?>

                     </td>
                  </tr>
               <?php endforeach ?>
            </tbody>
         </table>
      </div>
   </div>
   <div class="intervention_details_container hidden">
      <div class="content hidden">

         <h2>Intervention #1230</h2>
         <form id="interventionForm">
            <div class="form-row">
               <div class="form-group">
                  <label for="created_at">Date de la demande</label>
                  <div class="fixed-element" id="created_at"></div>
               </div>
               <div class="form-group">
                  <label for="updated_at">Date de la dernière mise à jour</label>
                  <div class="fixed-element" id="updated_at"></div>
               </div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="requester_user">Demandeur </label>
                  <input type="text" id="requester_user" name="requester_user" list="requester_user_list">
                  <datalist id="requester_user_list">
                     <?php foreach ($users as $user): ?>
                        <option data-value-id="<?= $user->id ?>" value="<?= $user->firstname . " " . $user->lastname ?>"><?= $user->firstname . " " . $user->lastname ?></option>
                     <?php endforeach; ?>
                  </datalist>
               </div>
               <div class="form-group">
                  <label for="intervention_target_user">Intervention pour</label>
                  <input type="text" id="intervention_target_user" name="intervention_target_user" list="intervention_target_user_list">
                  <datalist id="intervention_target_user_list">
                     <?php foreach ($users as $user): ?>
                        <option data-value-id="<?= $user->id ?>" value="<?= $user->firstname . " " . $user->lastname ?>"><?= $user->firstname . " " . $user->lastname ?></option>
                     <?php endforeach; ?>
                  </datalist>
               </div>
            </div>

            <div class="form-group">
               <label for="material">Intervention pour le matériel</label>
               <input type="text" id="material" name="material" list="material_list">
               <datalist id="material_list">
                  <?php foreach ($materials as $material): ?>
                     <option data-value-id="<?=$material->id?>" value="<?= $material->identificationNumber . " " . $material->identificationCode ?>"><?= $material->identificationNumber . " " . $material->identificationCode ?></option>
                  <?php endforeach; ?>
               </datalist>
            </div>

            <div class="form-group">
               <label>IP de la demande</label>
               <div class="fixed-element" id="request_ip">192.168.1.100</div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="intervention_type">Catégorie</label>
                  <select id="intervention_type" name="intervention_type">
                     <option value="">Sélectionner une catégorie</option>
                     <?php foreach ($interventionTypes as $type): ?>
                        <option value="<?= $type->id ?>"><?= $type->name ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="form-group">
                  <label for="intervention_subtype">Sous-catégorie</label>
                  <select id="intervention_subtype" name="intervention_subtype">
                     <option value="">Sélectionner une sous-catégorie</option>
                     <?php foreach ($interventionTypes as $type): ?>
                        <?php foreach ($type->subTypes as $subtype): ?>
                           <option data-intervention-type-id="<?= $type->id ?>" value="<?= $subtype->id ?>"><?= $subtype->name ?></option>
                        <?php endforeach; ?>
                     <?php endforeach; ?>
                  </select>
               </div>
            </div>

            <div class="form-group">
               <label for="keywords">Mots clés</label>
               <div class="breadcrumb_container">
                  <select id="keywords" name="keywords">
                     <option value="">Sélectionner un mot clé</option>
                     <?php foreach ($keywords as $keyword): ?>
                        <option value="<?= $keyword->id ?>"><?= $keyword->name ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="breadcrumb" id="breadcrumb_keywords">
                  </div>
               </div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="intervention_date">Intervention prévue le</label>
                  <input type="text" id="intervention_date" name="intervention_date">
               </div>
               <div class="form-group">
                  <label for="agenda_date">Agenda</label>
                  <input type="text" id="agenda_date" name="agenda_date">
               </div>
            </div>

            <div class="form-group">
               <label for="agenda_comments">Commentaire pour l'agenda</label>
               <textarea id="agenda_comments" name="agenda_comments" rows="4"></textarea>
            </div>

            <div class="form-group">
               <label for="helpers">Intervenants</label>
               <div class="breadcrumb_container">
                  <select id="helpers" name="helpers">
                     <option value="" selected>Sélectionner un intervenant</option>
                     <option value="all">Tous</option>
                     <?php foreach ($udiStaff as $staff): ?>
                        <option value="<?= $staff->id ?>"><?= $staff->name ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="breadcrumb" id="breadcrumb_helpers"></div>
               </div>
            </div>

            <div class="form-group">
               <label>Status</label>
               <div class="radio-group">
                  <?php foreach (InterventionState::getAll() as $state): ?>
                     <div class="radio-item">
                        <input type="radio" id="<?= $state ?>" name="status" value="<?= $state ?>">
                        <label for="<?= $state ?>"><?= $state ?></label>
                     </div>
                  <?php endforeach; ?>

               </div>
            </div>

            <div class="form-group">
               <label>Problème</label>
               <div class="fixed-element" id="problem">Description automatique du problème détecté</div>
            </div>

            <div class="form-group">
               <label for="titre">Titre</label>
               <input type="text" id="title" name="title">
            </div>

            <div class="form-group">
               <label for="comments">Commentaires</label>
               <textarea id="comments" name="comments" rows="4"></textarea>
            </div>

            <div class="form-group">
               <label for="solution">Solution</label>
               <textarea id="solution" name="solution" rows="4"></textarea>
            </div>

            <button type="submit" class="btn">Soumettre la demande</button>
         </form>
      </div>

   </div>
</div>
<?php $this->endSection() ?>