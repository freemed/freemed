<?php
 // file: iso-set.inc
 // note: ISO set checking, included by global.var.inc
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2
 // mod : 2000-07-25

if (!defined (__ISO_SET_INC__)) {

define (__ISO_SET_INC__, true);

  // ISO Handler
  switch ($language) {
    case "CS":   // CS - czech
    case "HU":   // HU - hungarian
    case "PL":   // PL - polish
    case "RO":   // RO - romanian
    case "HR":   // HR - croatian
    case "SK":   // SK - slovakian
    case "SL":   // SL - slovenian
      $__ISO_SET__ = "iso-8859-2"; break;    // ISO 8859-2
    case "EO":   // EO - esparanto
    case "MT":   // MT - maltese
      $__ISO_SET__ = "iso-8859-3"; break;    // ISO 8859-3
    case "ET":   // ET - estonian
    case "LV":   // LV - latvian
    case "LT":   // LT - lithuanian
    case "KL":   // KL - greenlandic
      $__ISO_SET__ = "iso-8859-4"; break;    // ISO 8859-4
    case "BG":   // BG - bulgarian
    case "BE":   // BE - byelorussian
    case "MK":   // MK - macedonian
    case "RU":   // RU - russian
    case "SR":   // SR - serbian
      $__ISO_SET__ = "iso-8859-5"; break;    // ISO 8859-5
    case "AR":   // AR - arabic
      $__ISO_SET__ = "iso-8859-6"; break;    // ISO 8859-6
    case "EL":   // EL - greek
      $__ISO_SET__ = "iso-8859-7"; break;    // ISO 8859-7
    case "IW":   // IW - hebrew
    case "JI":   // JI - yiddish
      $__ISO_SET__ = "iso-8859-8"; break;    // ISO 8859-8
    case "TR":   // TR - turkish
      $__ISO_SET__ = "iso-8859-9"; break;    // ISO 8859-9
    case "TH":   // TH - thai
      $__ISO_SET__ = "iso-8859-11"; break;   // ISO 8859-11
    case "CY":   // CY - gaelic/welsh
      $__ISO_SET__ = "iso-8859-14"; break;   // ISO 8859-14 
    case "EN":   // EN - english
    case "FR":   // FR - french
    case "ES":   // ES - spanish/castellano
    case "PT":   // PT - portuguese
    case "IT":   // IT - italian
    case "CA":   // CA - catalan
    case "SQ":   // SQ - albanian
    case "RM":   // RM - rhaeto-romanic
    case "NL":   // NL - dutch
    case "DE":   // DE - german
    case "DA":   // DA - danish
    case "SV":   // SV - swedish
    case "NO":   // NO - norwegian
    case "FI":   // FI - finnish
    case "FO":   // FO - faroese
    case "IS":   // IS - icelandic
    case "GA":   // GA - irish
    case "GD":   // GD - scottish
    default:
      $__ISO_SET__ = "iso-8859-1"; break;    // english ISO set (8859-1)
  } // end ISO handler switch

} // end checking for __ISO_SET_INC__

?>
