<?php

if ( ! defined( 'ABSPATH' ) ) exit;

enum Barcode: string {
    case Type_2S = '2S';
    case Type_3S = '3S';
    case Type_CC = 'CC';
    case Type_CP = 'CP';
    case Type_CD = 'CD';
    case Type_CF = 'CF';
    case Type_LA = 'LA';
    case Type_RI = 'RI';
    case Type_E  = 'UE';
};