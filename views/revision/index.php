<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">

    <title>Revision</title>
</head>

<body>

<?php require_once __DIR__ . "/../layout/header.php"; ?>

<!-- /////////////////////////////////////////////////////// -->

<div class="page-header">
<h1>Revision de habitaciones</h1>
<p>Administración de revision de habitaciones del hotel</p>
</div>

<!-- /////////////////////////////////////////////////////// -->

<div class="container">
<input type="text" id="buscador" placeholder="Buscar..."
value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

<br><br>

<?php

$habitacionesAgrupadas = [];

foreach ($faltantes as $f) {

    $numeroHabitacion = $f['numero'];

    if (!isset($habitacionesAgrupadas[$numeroHabitacion])) {

        $habitacionesAgrupadas[$numeroHabitacion] = [

            'numero' => $f['numero'],
            'tipo' => $f['tipo'],
            'items' => []

        ];
    }

    $habitacionesAgrupadas[$numeroHabitacion]['items'][] = $f;
}
?>

<div class="revision-grid">

<?php foreach($habitacionesAgrupadas as $hab): ?>

    <?php

    $faltantesHabitacion = array_filter(
        $hab['items'],
        fn($item) => $item['faltantes'] > 0
    );

    $estaCompleta = count($faltantesHabitacion) == 0;
    ?>

    <div class="habitacion-card">

        <!-- HEADER CARD -->
        <div class="habitacion-card-header">
            <div>
                <h2>Habitación <?= $hab['numero'] ?></h2>
                <p><?= $hab['tipo'] ?></p>
            </div>

            <div class="<?= $estaCompleta ? 'estado-ok' : 'estado-faltante' ?>">
                <?= $estaCompleta ? 'Completa ✓' : 'Con faltantes' ?>
            </div>

        </div>

        <!-- ITEMS -->

        <div class="habitacion-items">
            <?php foreach($hab['items'] as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <strong><?= $item['articulo'] ?></strong>
                        <span>

                            <?= $item['cantidad_actual'] ?>
                            /
                            <?= $item['cantidad_base'] ?>

                        </span>
                    </div>
                    <div>

                        <?php if($item['faltantes'] > 0): ?>
                            <span class="badge-faltante">
                                Faltan <?= $item['faltantes'] ?>

                            </span>
                        <?php else: ?>
                            <span class="badge-ok">

                                Completo

                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

</div>


</div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<script>
const buscador = document.getElementById('buscador');
function filtrar() {

    let texto = buscador.value.toLowerCase();
    let cards = document.querySelectorAll(".habitacion-card");

    cards.forEach(function(card){
        let contenido = card.textContent.toLowerCase();
        if(contenido.includes(texto)){
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}

buscador.addEventListener('keyup', filtrar);
document.addEventListener('DOMContentLoaded', function() {

    if (buscador.value.trim() !== '') {
        filtrar();
    }

});

</script>
</body>
</html>