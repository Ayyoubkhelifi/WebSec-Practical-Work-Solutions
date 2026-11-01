<?php
// TP 01 — Partie E, étape 16 : redirection.
// Renvoyer Location: index.php et un statut 302, puis arrêter le traitement.

header('Location: index.php', true, 302);
exit;
