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
                     <td><?= $this->escape($intervention->subtypeName ?? $intervention->typeName ?? "-") ?></td>
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

         <h2 id="intervention_title"><a href="#">Intervention #1230</a></h2>
         <form id="intervention_form">
            <div class="form_row">
               <div class="form_group">
                  <label for="created_at">Date de la demande</label>
                  <div class="fixed-element" id="created_at"></div>
               </div>
               <div class="form_group">
                  <label for="updated_at">Date de la dernière mise à jour</label>
                  <div class="fixed-element" id="updated_at"></div>
               </div>
            </div>

            <div class="form_row">
               <div class="form_group">
                  <label for="requester_user">Demandeur <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/></svg></a></label>
                  <input type="text" id="requester_user" name="requester_user" list="requester_user_list">
                  <input type="hidden" id="requester_user_id" name="requester_user_id">
                  <datalist id="requester_user_list">
                     <?php foreach ($users as $user): ?>
                        <?php $requesterUserValue = "[" . $user->ulgId . "] " . $user->firstname . ($user->firstname ? " " : "") . $user->lastname ?>
                        <option data-value-id="<?= $user->id ?>" value="<?= $requesterUserValue ?>"></option>
                     <?php endforeach; ?>
                  </datalist>
               </div>
               <div class="form_group">
                  <label for="intervention_target_user">Intervention pour <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/></svg></a></label>
                  <input type="text" id="intervention_target_user" name="intervention_target_user" list="intervention_target_user_list">
                  <input type="hidden" id="intervention_target_user_id" name="intervention_target_user_id">
                  <datalist id="intervention_target_user_list">
                     <?php foreach ($users as $user): ?>
                        <?php $requesterUserValue = "[" . $user->ulgId . "] " . $user->firstname . ($user->firstname ? " " : "") . $user->lastname ?>
                        <option data-value-id="<?= $user->id ?>" value="<?= $requesterUserValue ?>"></option>
                     <?php endforeach; ?>
                  </datalist>
               </div>
            </div>

            <div class="form_group">
               <label for="material">Intervention pour le matériel <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z"/></svg></a></label>
               <input type="text" id="material" name="material" list="material_list">
               <input type="hidden" id="material_id" name="material_id">
               <datalist id="material_list">
                  <?php foreach ($materials as $material): ?>
                     <option data-value-id="<?= $material->id ?>" value="<?= $material->identificationNumber . " " . $material->identificationCode ?>"><?= $material->identificationNumber . " " . $material->identificationCode ?></option>
                  <?php endforeach; ?>
               </datalist>
            </div>

            <div class="form_group">
               <label>IP de la demande</label>
               <div class="fixed-element" id="request_ip">192.168.1.100</div>
            </div>

            <div class="form_row">
               <div class="form_group">
                  <label for="intervention_type">Catégorie</label>
                  <select id="intervention_type" name="intervention_type">
                     <option value="">Sélectionner une catégorie</option>
                     <?php foreach ($interventionTypes as $type): ?>
                        <option value="<?= $type->id ?>"><?= $type->name ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="form_group">
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

            <div class="form_group">
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

            <div class="form_row">
               <div class="form_group">
                  <label for="intervention_date">Intervention prévue le</label>
                  <div class="date_picker" data-min-year="2000" data-max-year="2030" data-add-time="true" data-default-date-if-empty="false">
                     <input type="text" id="intervention_date" value="" name="intervention_date">
                  </div>
               </div>
               <div class="form_group">
                  <label for="agenda_date">Agenda</label>
                  <div class="date_picker" data-min-year="2000" data-max-year="2030" data-add-time="true" data-default-date-if-empty="false">
                     <input type="text" id="agenda_date" name="agenda_date" value="">
                  </div>
               </div>
            </div>

            <div class="form_group">
               <label for="agenda_comments">Commentaire pour l'agenda</label>
               <textarea id="agenda_comments" name="agenda_comments" rows="4"></textarea>
            </div>

            <div class="form_group">
               <label for="helpers">Intervenants</label>
               <div class="breadcrumb_container">
                  <select id="helpers" name="helpers">
                     <option value="" selected>Sélectionner un intervenant</option>
                     <option value="all">Tous</option>
                     <?php foreach ($udiStaff as $staff): ?>
                        <option value="<?= $staff->id ?>"><?= $staff->surname ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="breadcrumb" id="breadcrumb_helpers"></div>
               </div>
            </div>

            <div class="form_group">
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

            <div class="form_group">
               <label>Problème</label>
               <div class="fixed-element" id="problem">Description automatique du problème détecté</div>
            </div>

            <div class="form_group">
               <label for="titre">Titre</label>
               <input type="text" id="title" name="title">
            </div>

            <div class="form_group">
               <label for="comments">Commentaires</label>
               <textarea id="comments" name="comments" rows="4"></textarea>
            </div>

            <div class="form_group">
               <label for="solution">Solution</label>
               <textarea id="solution" name="solution" rows="4"></textarea>
            </div>

            <button type="submit" class="btn">Soumettre la demande</button>
         </form>
      </div>

   </div>
</div>
<?php $this->endSection() ?>