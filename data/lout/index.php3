<?php
  # file: data/lout/index.php3
  # note: removes prying eyes from lout directory
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  include ("../../global.var.inc"); // include global variables

  Header("Location: $complete_url"); // redirect to real root
?>
