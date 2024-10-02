<style>
    @media screen and (max-width: 767px) {
        .user-hidden {
            display: none;
        }
    }
</style>
<header class="gpem-hide-print">
    <div style="display: flex; align-items: center;">
        <div style="margin-right: 10px; width: 25px;">
            <span id="button-menu" class="fas fa-bars" onclick="fnMenuToggle(); return false;"></span>
        </div>       
        <div>
            <img src="/mycloud/logos/logo-gpem.png" alt="" height="40">
        </div>
        <div style="align-self: center; flex: 1 0 0%;">
            <label class="menu-user m-0"><a class="btn btn-outline-danger btn-sm text-white font-weight-bold" href="/gesman/Salir.php" role="button"><i class="fas fa-sign-out-alt"></i><span class="user-hidden"><?php echo $_SESSION['UserName']; ?></span></a></label>
        </div>
    </div>
    <div id="overlay" onclick="fnOcultarFondoMenu(); return false;"></div>
    <nav class="navegacion">
        <ul class="menu ps-0">
            <li class="title-menu">Menú</li>
            <li><a href="/gesman/Empresas.php" id="MenuEmpresas"><i class="fas fa-building icon-menu"></i> Empresas</a></li>
            <li><a href="/gesman/Ordenes.php" id="MenuOrdenes"><i class="fas fa-list icon-menu"></i> Ordenes</a></li>
            <li><a href="/informes/Informes.php" id="MenuInformes"><i class="fas fa-list icon-menu"></i> Informes</a></li>
            <li><a href="/checklist/Checklists.php" id="MenuChecklists"><i class="fas fa-list icon-menu"></i> CheckLists</a></li>
            <li><a href="/checklist/admin/Plantillas.php" id="MenuPlantillas"><i class="fas fa-list icon-menu"></i> Plantillas</a></li>
            <!--
            <li class="item-submenu" menu="2">
                <a href="#" id="MenuSistema" onclick="fnMostrarSubmenu(this); return false;"><span class="fas fa-cog"></span> Sistema</a>
                <ul class="submenu ps-0">
                    <li class="title-menu"><span class="fas fa-vials icon-menu"></span> Muestras</li>
                    <li class="go-back" onclick="fnOcultarSubmenu(this); return false;">Atras</li>
                    <li><a href="/portal/cotizador/admin/Clientes.php" id="MenuSistemaClientes"><span class="fas fa-user-tag icon-menu"></span> Clientes</a></li>
                </ul>
            </li>
            -->
            <li><a href="/gesman/Salir.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
        </ul>
    </nav>
</header>