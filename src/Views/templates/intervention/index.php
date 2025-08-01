<?php

/** @var \App\Support\PaginatedResult<\App\Intervention\DTOs\InterventionDto> $paginatedResults */
/** @var \App\User\DTOs\UserStaffDto[] $udiStaff */

use App\Intervention\InterventionState;
?>

<?php $this->extend('layout') ?>

<?php $this->section('title') ?>Liste des interventions<?php $this->endSection() ?>

<?php $this->section('style') ?>
<link rel="stylesheet" href="./styles/intervention/index.css">
<?php $this->endSection() ?>

<?php $this->section('script') ?>
<script src="./scripts/intervention/index.js"></script>
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
                     <td><?= $this->escape($intervention->title ?? "-") ?></td>
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
   <div class="intervention_details_container ">
      <div class="content ">

         <h2>Intervention #1230</h2>
         <form id="interventionForm">
            <div class="form-row">
               <div class="form-group">
                  <label for="datedemande">Date de la demande</label>
                  <input type="text" id="datedemande" name="datedemande">
               </div>
               <div class="form-group">
                  <label for="datemaj">Date de la dernière mise à jour</label>
                  <input type="text" id="datemaj" name="datemaj">
               </div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="demandeur">Demandeur</label>
                  <input type="text" id="demandeur" name="demandeur" list="demandeur-list">
                  <datalist id="demandeur-list">
                     <option value="Jean Dupont">
                     <option value="Marie Martin">
                     <option value="Pierre Bernard">
                     <option value="Sophie Dubois">
                  </datalist>
               </div>
               <div class="form-group">
                  <label for="intervention">Intervention pour</label>
                  <input type="text" id="intervention" name="intervention" list="intervention-list">
                  <datalist id="intervention-list">
                     <option value="Maintenance préventive">
                     <option value="Réparation urgente">
                     <option value="Installation">
                     <option value="Configuration">
                  </datalist>
               </div>
            </div>

            <div class="form-group">
               <label for="material">Intervention pour le matériel</label>
               <input type="text" id="material" name="material" list="material-list">
               <datalist id="material-list">
                  <option value="Ordinateur portable">
                  <option value="Serveur">
                  <option value="Imprimante">
                  <option value="Réseau">
                  <option value="Téléphone">
               </datalist>
            </div>

            <div class="form-group">
               <label>IP de la demande</label>
               <div class="fixed-element" id="ipdemande">192.168.1.100</div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="categorie">Catégorie</label>
                  <select id="categorie" name="categorie">
                     <option value="">Sélectionner une catégorie</option>
                     <option value="materiel">Matériel</option>
                     <option value="logiciel">Logiciel</option>
                     <option value="reseau">Réseau</option>
                     <option value="securite">Sécurité</option>
                  </select>
               </div>
               <div class="form-group">
                  <label for="souscategorie">Sous-catégorie</label>
                  <select id="souscategorie" name="souscategorie">
                     <option value="">Sélectionner une sous-catégorie</option>
                     <option value="panne">Panne</option>
                     <option value="installation">Installation</option>
                     <option value="configuration">Configuration</option>
                     <option value="mise-a-jour">Mise à jour</option>
                  </select>
               </div>
            </div>

            <div class="form-group">
               <label for="motscles">Mots clés</label>
               <div class="breadcrumb-container">
                  <select id="motscles" name="motscles">
                     <option value="">Ajouter un mot clé</option>
                     <option value="urgent">Urgent</option>
                     <option value="critique">Critique</option>
                     <option value="maintenance">Maintenance</option>
                     <option value="bug">Bug</option>
                     <option value="performance">Performance</option>
                     <option value="securite">Sécurité</option>
                  </select>
                  <div class="breadcrumb" id="motscles-breadcrumb"></div>
               </div>
            </div>

            <div class="form-row">
               <div class="form-group">
                  <label for="interventionprevue">Intervention prévue le</label>
                  <input type="text" id="interventionprevue" name="interventionprevue">
               </div>
               <div class="form-group">
                  <label for="agenda">Agenda</label>
                  <input type="text" id="agenda" name="agenda">
               </div>
            </div>

            <div class="form-group">
               <label for="commentaire">Commentaire pour l'agenda</label>
               <textarea id="commentaire" name="commentaire" rows="4"></textarea>
            </div>

            <div class="form-group">
               <label for="intervenants">Intervenants</label>
               <div class="breadcrumb-container">
                  <select id="intervenants" name="intervenants">
                     <option value="" selected>-</option>
                     <?php foreach ($udiStaff as $staff): ?>
                        <option value="<?= $staff->id ?>"><?= $staff->name ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="breadcrumb" id="intervenants-breadcrumb"></div>
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
               <div class="fixed-element" id="probleme">Description automatique du problème détecté</div>
            </div>

            <div class="form-group">
               <label for="titre">Titre</label>
               <input type="text" id="titre" name="titre">
            </div>

            <div class="form-group">
               <label for="commentaires">Commentaires</label>
               <textarea id="commentaires" name="commentaires" rows="4"></textarea>
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