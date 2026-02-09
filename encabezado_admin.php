<?php
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>La caja de letras</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fff7f0;
      color: #4a2c0f;
      margin: 0;
      padding: 0;
    }

    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #3bb2ccff;
      padding: 0.5rem 1rem;
      box-shadow: 0 2px 5px rgba(72, 214, 230, 0.4);
      position: sticky;
      top: 0;
      z-index: 1000;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: white;
      font-weight: bold;
      font-size: 1.2rem;
      user-select: none;
    }

    .logo img {
      height: 40px;
      width: auto;
      border-radius: 5px;
      background-color: white;
      padding: 2px;
    }

    .nav-title {
      color: white;
      font-size: 1.5rem;
      font-weight: 700;
      user-select: none;
      text-align: center;
      flex-grow: 1;
    }

    .nav-menu {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .nav-link {
      display: flex;
      flex-direction: column;
      align-items: center;
      color: white;
      font-size: 0.75rem;
      text-decoration: none;
      transition: opacity 0.3s ease;
    }

    .nav-link:hover {
      opacity: 0.8;
    }

    .nav-icon {
      width: 24px;
      height: 24px;
      margin-bottom: 2px;
    }

    @media (max-width: 600px) {
      .nav-link span {
        display: none;
      }
    }

    footer {
      background-color:  #3bb2ccff;
      color: white;
      text-align: center;
      padding: 1rem 0;
      font-weight: 600;
      box-shadow: 0 -2px 5px rgba(66, 40, 21, 0.4);
      user-select: none;
    }
  </style>
</head>
<body>
  <nav>
  <div class="logo">
    <img src="imagenes/logouni.png" alt="Logo Universidad" />
    La Caja de Letras
  </div>

  <div class="nav-title">Un lugar para leer, soñar y aprender</div>

  <div class="nav-menu">
    <a href="menuadmin.php" class="nav-link">
      <svg class="nav-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-6v-6H10v6H4a1 1 0 0 1-1-1V9.5Z" fill="white"/>
      </svg>
      <span>Inicio</span>
    </a>

    <a href="cuenta_admin.php" class="nav-link">
      <svg class="nav-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5Z" fill="white"/>
      </svg>
      <span>Cuenta</span>
    </a>
  </div>
</nav>

