<?php
  # file: scripts/index.php3
  # note: removes prying eyes from scripts directory
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  include ("../global.var.inc"); // include global variables

  Header("Location: $complete_url"); // redirect to real root
?>
