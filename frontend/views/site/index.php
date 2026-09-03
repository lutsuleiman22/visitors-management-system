<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Visitor Management System';
$this->params['meta_description'] = 'Self-service visitor check-in and check-out portal.';
?>
<div class="site-index">
    <div class="hero-banner text-white rounded-4 p-5 mb-4 position-relative overflow-hidden">
        <div class="position-relative">
            <h1 class="display-5 fw-bold mb-3">Visitor Management</h1>
            <p class="lead opacity-75 mb-4 hero-lead">
                Check in at reception, capture your photo, and receive a printable visitor pass with QR code.
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <?= Html::a('Visitor Check-In', ['/visitor/check-in'], [
                    'class' => 'btn btn-light btn-lg fw-semibold px-4',
                ]) ?>
                <?= Html::a('Visitor Check-Out', ['/visitor/check-out'], [
                    'class' => 'btn btn-outline-light btn-lg px-4',
                ]) ?>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold">1. Check In</h2>
                    <p class="text-body-secondary small mb-0">
                        Enter your details, select your host, and capture a webcam photo.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold">2. Get Your Pass</h2>
                    <p class="text-body-secondary small mb-0">
                        Print or save your badge with a unique QR code for security and checkout.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold">3. Check Out</h2>
                    <p class="text-body-secondary small mb-0">
                        Scan your QR code or search by name / National ID when leaving.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
