<?php
namespace App\Service;

class GeneradorDeMensajes {
  public function getMensaje(string $message, string $data): string
  {
    return $message . $data;
  }
}

