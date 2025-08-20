<?php

/** @var \App\Support\PaginatedResult<\App\Modules\Intervention\DTOs\InterventionDto> $paginatedResults */
/** @var \App\Modules\User\DTOs\UserStaffDto[] $udiStaff */
/** @var \App\Modules\Keyword\DTOs\KeywordDto[] $keywords */
/** @var \App\Modules\Intervention\DTOs\InterventionTypeDto[] $interventionTypes */
/** @var \App\Modules\Material\DTOs\MaterialDto[] $materials */
/** @var array $pagesDisplay */

use App\Helpers\StringHelper;
use App\Modules\Intervention\Const\InterventionState;
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
            <div class="flex_table">
                <!-- Header -->
                <div class="table_header">
                    <div class="table_cell column_id">Id</div>
                    <div class="table_cell column_date">Date</div>
                    <div class="table_cell">Personne</div>
                    <div class="table_cell service_column">Service</div>
                    <div class="table_cell column_helper">Intervenants</div>
                    <div class="table_cell category_column">Sujet</div>
                    <div class="table_cell">Titre</div>
                    <div class="table_cell status_column">Status</div>
                </div>
                <?php
                $interventions = $paginatedResults->data;
                foreach ($interventions as $intervention):
                    $interventionUrl = $intervention->id;
                ?>
                    <div class="table_row intervention_row" data-intervention-id="<?= $this->escape($intervention->id) ?>">
                        <div class="table_cell column_id">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <b><?= $this->escape($intervention->id) ?></b>
                            </a>
                        </div>
                        <div class="table_cell column_date">
                            <?php
                            $datetime = DateTime::createFromFormat("Y-m-d H:i:s", $intervention->requestDate);
                            $date = $this->escape($datetime->format("d/m/Y"));
                            $time = $this->escape($datetime->format("H:i"));
                            ?>
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <div><b><?= $date ?></b></div>
                                <div><span><?= $time ?></span></div>
                            </a>
                        </div>
                        <div class="table_cell column_target">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?= $this->escape($intervention->targetUserName) ?>
                            </a>
                        </div>
                        <div class="table_cell service_column">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?php foreach ($intervention->services as $service): ?>
                                    <div><span><?= $this->escape($service->name) ?></span></div>
                                <?php endforeach ?>
                            </a>
                        </div>
                        <div class="table_cell column_helper">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?php if ($intervention->helpers == []) echo "-" ?>
                                <?php foreach ($intervention->helpers as $helper): ?>
                                    <div><span><?= $this->escape($this->truncate($helper->surname, 4, "")) ?></span></div>
                                <?php endforeach ?>
                            </a>
                        </div>
                        <div class="table_cell category_column">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?= $this->escape($intervention->subtypeName ?? $intervention->typeName ?? "-") ?>
                            </a>
                        </div>
                        <div class="table_cell">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?= $this->escape($this->truncate($intervention->title ?? "-", 30)) ?>
                            </a>
                        </div>
                        <div class="table_cell status_column">
                            <a href="<?= $this->escape($interventionUrl) ?>">
                                <?php if ($intervention->status == InterventionState::RECEIVED): ?>
                                    <b class="text_info"><?= $this->escape($intervention->status) ?></b>
                                <?php elseif ($intervention->status == InterventionState::CLOSED): ?>
                                    <b class="text_danger"><?= $this->escape($intervention->status) ?></b>
                                <?php elseif ($intervention->status == InterventionState::PERSISTENT): ?>
                                    <b class="text_warning"><?= $this->escape($intervention->status) ?></b>
                                <?php elseif ($intervention->status == InterventionState::IN_PROGRESS): ?>
                                    <b class="text_success"><?= $this->escape($intervention->status) ?></b>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="intervention_page_selection">
            <?php
            $hasPreviousPages = count($pagesDisplay) > 0 && $pagesDisplay[0] != 1;
            $hasNextPages = count($pagesDisplay) > 0 && $pagesDisplay[count($pagesDisplay) - 1] != $paginatedResults->lastPage;
            ?>
            <?php
            if ($hasPreviousPages)
                echo "<span class='dotted_page'>. . .</span>"
            ?>
            <?php foreach ($pagesDisplay as $page): ?>
                <?php if ($page == $paginatedResults->currentPage): ?>
                    <a href="?page=<?=$page?>" class="active"><?= $page ?></a>
                <?php else: ?>
                    <a href="?page=<?=$page?>"><?= $page ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php
            if ($hasNextPages)
                echo "<span class='dotted_page'>. . .</span>"
            ?>
        </div>
    </div>


    <!-- SIDE PANEL when clicking on a row -->
    <div class="intervention_details_container hidden">
        <div class="content hidden">
            <div class="close_button_container">
                <button title="Rabattre le panneau latéral" id="close_intervention_details_container_button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M409 337C418.4 327.6 418.4 312.4 409 303.1L265 159C258.1 152.1 247.8 150.1 238.8 153.8C229.8 157.5 224 166.3 224 176L224 256L112 256C85.5 256 64 277.5 64 304L64 336C64 362.5 85.5 384 112 384L224 384L224 464C224 473.7 229.8 482.5 238.8 486.2C247.8 489.9 258.1 487.9 265 481L409 337zM416 480C398.3 480 384 494.3 384 512C384 529.7 398.3 544 416 544L480 544C533 544 576 501 576 448L576 192C576 139 533 96 480 96L416 96C398.3 96 384 110.3 384 128C384 145.7 398.3 160 416 160L480 160C497.7 160 512 174.3 512 192L512 448C512 465.7 497.7 480 480 480L416 480z" />
                    </svg>
                </button>
            </div>
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
                        <label for="requester_user">Demandeur <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                    <path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z" />
                                </svg></a></label>
                        <input type="text" id="requester_user" name="requester_user" list="requester_user_list">
                        <input type="hidden" id="requester_user_id" name="requester_user_id">
                        <datalist id="requester_user_list">

                        </datalist>
                    </div>
                    <div class="form_group">
                        <label for="intervention_target_user">Intervention pour <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                    <path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z" />
                                </svg></a></label>
                        <input type="text" id="intervention_target_user" name="intervention_target_user" list="intervention_target_user_list">
                        <input type="hidden" id="intervention_target_user_id" name="intervention_target_user_id">
                        <datalist id="intervention_target_user_list">

                        </datalist>
                    </div>
                </div>

                <div class="form_group">
                    <label for="material">Intervention pour le matériel <a target="_blank" href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                <path d="M384 64C366.3 64 352 78.3 352 96C352 113.7 366.3 128 384 128L466.7 128L265.3 329.4C252.8 341.9 252.8 362.2 265.3 374.7C277.8 387.2 298.1 387.2 310.6 374.7L512 173.3L512 256C512 273.7 526.3 288 544 288C561.7 288 576 273.7 576 256L576 96C576 78.3 561.7 64 544 64L384 64zM144 160C99.8 160 64 195.8 64 240L64 496C64 540.2 99.8 576 144 576L400 576C444.2 576 480 540.2 480 496L480 416C480 398.3 465.7 384 448 384C430.3 384 416 398.3 416 416L416 496C416 504.8 408.8 512 400 512L144 512C135.2 512 128 504.8 128 496L128 240C128 231.2 135.2 224 144 224L224 224C241.7 224 256 209.7 256 192C256 174.3 241.7 160 224 160L144 160z" />
                            </svg></a></label>
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
                        <div class="date_picker" data-min-year="2000" data-max-year="2030" data-is-fixed="true" data-add-time="true" data-default-date-if-empty="false">
                            <input type="text" id="intervention_date" value="" name="intervention_date">
                        </div>
                    </div>
                    <div class="form_group">
                        <label for="agenda_date">Agenda</label>
                        <div class="date_picker" data-min-year="2000" data-max-year="2030" data-is-fixed="true" data-add-time="true" data-default-date-if-empty="false">
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

                <hr class="message_separator">


                <!-- MESSAGE -->
                <h3 class="message_title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                        <path d="M64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L360 512C354.8 512 349.8 513.7 345.6 516.8L230.4 603.2C226.2 606.3 221.2 608 216 608C202.7 608 192 597.3 192 584L192 512L160 512C107 512 64 469 64 416z" />
                    </svg>
                    Messages :
                </h3>

                <div class="messages_container">

                    <div class="message_container">
                        <div class="message_header">
                            <div class="message_index">#1</div>
                            <div class="message_meta">
                                <div class="author_info">
                                    <div class="author_avatar">JD</div>
                                    <div>
                                        <div class="author_name">John Doe</div>
                                        <div class="message_date">March 15, 2024 at 2:30 PM</div>
                                    </div>
                                </div>
                                <div class="visibility_indicator visibility_public">
                                    <svg class="visibility_icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    Publique
                                </div>
                            </div>
                        </div>
                        <div class="message_content">
                            <p>This is a sample message post that demonstrates the styling. The design is clean and modern, with proper spacing and typography that's easy to read.</p>
                            <p>The header contains all the metadata including author, date, and visibility status, while the content area provides a comfortable reading experience.</p>
                        </div>
                    </div>

                    <!-- Example of a private post -->
                    <div class="message_container" style="margin-top: 20px;">
                        <div class="message_header">
                            <div class="message_meta">
                                <div class="author_info">
                                    <div class="author_avatar" style="background-color: blue;">AS</div>
                                    <div>
                                        <div class="author_name">Alice Smith</div>
                                        <div class="message_date">March 14, 2024 at 11:45 AM</div>
                                    </div>
                                </div>
                                <div class="visibility_indicator visibility_private">
                                    <svg class="visibility_icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Visible pour l'UDI <br>uniquement.
                                </div>
                            </div>
                        </div>
                        <div class="message_content">
                            <p>This is an example of a private comment post. Notice how the visibility indicator changes color and icon to reflect the privacy status.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn">Soumettre la demande</button>
            </form>
        </div>

    </div>
</div>
<?php $this->endSection() ?>