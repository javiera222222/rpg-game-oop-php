<?php

declare(strict_types=1);
require_once 'Jugador.php';
require_once 'Auxiliares/GeneradorDePersonajes.php';
require_once 'Dado.php';

class Juego
{
    /*
    Pueden crear nuevos atributos y metodos para esta clase de ser necesarios
    recuerden que tiene que ser privados y detallar el porque los usan
    */
    private static int $estado = self::NO_INICIADO;
    /** @var Personaje[] */
    // lista de personajes para seleccionar
    private static array $listadoDeHeroes = [];
    /** @var Personaje[] */
    private static array $listadeMonstruos = [];
    private static int $rondas = 0;
    /** @var Jugador[]*/
    private static array $lista_jugadores = [];
    //cree esta variable para poder acceder al objeto dado en el metodo iniciarCombate
    /** @var Dado */
    private static  object $dado;

    //cree este arreglo asociativo para organizar los heroes por jugador, usando el arreglo con la lista de jugadores como clave y el arreglo equipoDeheroes como valor por cada jugador
    /** @var Personaje[] */
    private static  array $heroesPorJugador = [];
    //cree estea arreglo ya tener separados a los mostruos que van a participar en cada jugada
    /** @var Personaje[] */
    private static array $monstruosPorRonda = [];
    //cree esta variable numerica para poder tener definida la cantidad de jugadores totales por jugada
    private static int $cantJugadores;
    //cree esta variable numerica para poder tener definida la cantidad de heroes totales por jugada
    private static int $cantHeroesTotal;
    //cree estea arreglo para tener definida la cantidad de heroes que va a tener cada jugador por jugada
    private static array $equipoDeHeroes = [];
    //cree estos dos arreglos para guaradar la cantidad vida con la que empiezan a jugar los personajes para asi poder compararla con la que tienen despues de jugar
    private static array $vidaOriginalM = [];
    private static array $vidaOriginalH = [];







    // valores para estado
    const MAX_NUM = 3;
    const MIN_NUM = 1;
    const INICIADO = 1;
    const JUGANDO = 2;
    const NO_INICIADO = -1;
    const TERMINADO = 0;
    const ENPAUSA = 3;
    const REINICIAR = 1;




    public static function iniciar(): void
    {
        if (self::$estado === self::NO_INICIADO) {
            self::$cantJugadores  = intval(readline("cuantos jugadores participan?(de 1 a 3) \n"));

            $cantHeroes = intval(readline("Con cuantos heroes va a jugar cada unx?(de 1 a 3)\n"));
            echo "----------------------------------------------\n";
            self::$cantHeroesTotal = $cantHeroes * self::$cantJugadores;

            $cantMostruos = self::$cantHeroesTotal / 3;


            if (self::$cantJugadores >= self::MIN_NUM && self::$cantJugadores <= self::MAX_NUM && $cantHeroes >= self::MIN_NUM && $cantHeroes <= self::MAX_NUM) {

                self::crearPersonajes();
                self::agregarArmas();
                $count = 1;
                echo "De la lista de mostruos:\n" . GeneradorDePersonajes::MONSTRUOS . "\n";
                foreach (self::$listadeMonstruos as $monstruo) {
                    echo  $count . " - " . $monstruo->getNombre() . " - Tipo: " . get_class($monstruo) . PHP_EOL;



                    $count++;
                }
                echo "\nSe tendran que enfrentar en esta oportunidad a: \n";
                for ($i = 0; $i < $cantMostruos; $i++) {

                    $indMonstruo = array_rand(static::$listadeMonstruos);
                    array_push(self::$monstruosPorRonda, static::$listadeMonstruos[$indMonstruo]);
                    self::$vidaOriginalM[] = self::$monstruosPorRonda[$i]->getVida();

                    echo    " - " . self::$monstruosPorRonda[$i]->getNombre() . " - Tipo: " . get_class(self::$monstruosPorRonda[$i]) . PHP_EOL;
                }
                echo "----------------------------------------------\n";






                for ($j = 0; $j < self::$cantJugadores; $j++) {
                    $nombreJugador = readline("Ingresa tu nombre: \n");
                    $jugador  = new Jugador($j, $nombreJugador, 0);
                    self::$lista_jugadores[$j] = $jugador;
                    $id = $jugador->getID();
                    echo "El jugador:  " . $jugador->getNombreApellido() . " sera el numero:    " . $id . "\n";



                    for ($ii = 0; $ii < $cantHeroes; $ii++) {


                        echo "----------------------------------\n";
                        echo "\nElegi tus heroes de esta lista:" . GeneradorDePersonajes::HEROES . "\n";
                        foreach (self::$listadoDeHeroes as $i => $heroe) {
                            echo $i . " - " . $heroe->getNombre() . " - Tipo: " . get_class($heroe) . PHP_EOL;
                        }
                        $ind = intval(readline());

                        self::$equipoDeHeroes[] = self::$listadoDeHeroes[$ind];
                        self::$vidaOriginalH[] = self::$listadoDeHeroes[$ind]->getVida();
                        unset(self::$listadoDeHeroes[$ind]);
                    }


                    self::$heroesPorJugador[$id] = self::$equipoDeHeroes;
                }
            } else {
                echo "Recorda que podes seleccionar de 1 a 3 jugadores  y personajes\n";
            }
        }
        self::$estado = self::JUGANDO;
        self::realizarRonda();
        return;
    }



    private static function realizarRonda()
    {


        if (self::$estado === self::JUGANDO) {


            self::iniciarCombate();
            self::listarPersonajes();
            self::mostrarGanador();
            self::finalizarJuego();
        }
        self::reiniciar();
        self::juegoNuevo();
    }

    /* 
metodo para crear los personajes que luego se agregaran al equipo de cada jugador
y al equipo de monstruos
*/
    private static function crearPersonajes(): void
    {
        self::$listadoDeHeroes = GeneradorDePersonajes::crearPersonajes(GeneradorDePersonajes::HEROES);
        self::$listadeMonstruos = GeneradorDePersonajes::crearPersonajes(GeneradorDePersonajes::MONSTRUOS);
    }
    /*
Metodo para agregar armas a los heroes
*/
    private static function agregarArmas(): void
    {
        GeneradorDePersonajes::agregarArmas(self::$listadoDeHeroes);
    }

    /*
en este método se realiza la acción de atacar y 
defender. un personaje de la lista de héroes de cada jugador 
atacará a un personaje de la lista de monstruos, esto puede 
ser al azar o elegido por el usuario (es una opción abierta)
*/
    private static function iniciarCombate(): void
    {
        if (self::$estado == self::JUGANDO) {
            for ($ii = 0; $ii < self::$cantJugadores; $ii++) {
                self::$dado = new dado();
                for ($i = 1; $i < count(self::$equipoDeHeroes); $i++) {


                    $dadoJugador =    self::$dado->mostrarDado();

                    $dadoMostruo =    self::$dado->mostrarDado();



                    foreach (self::$heroesPorJugador as self::$lista_jugadores[$ii] => self::$equipoDeHeroes) {


                        foreach (self::$equipoDeHeroes as $heroe) {
                            $heroeActual = self::$equipoDeHeroes[$i];
                            $mostruoActual = self::$monstruosPorRonda[$ii];
                            $indiceM = array_search($mostruoActual, self::$monstruosPorRonda);
                            $indiceH = array_search($heroeActual, self::$equipoDeHeroes);
                            if ($dadoJugador > $dadoMostruo) {

                                $mostruoActual->defender($heroeActual);


                                self::verificarVida($indiceM);
                                self::registroCombate($indiceH, $indiceM);
                                self::enPausa();
                            } else if ($dadoJugador < $dadoMostruo) {

                                $heroe->defender($mostruoActual);

                                self::verificarVida($indiceH);
                                self::registroCombate($indiceM, $indiceH);
                                self::enPausa();
                            }
                        }
                    }
                }
            }
        }
    }


    /* 
después de cada combate se debe verificar la vida del personaje 
que defendió, si esta es 0 o menor se deberá devolver true en caso 
que siga con vida o false en caso contrario.
*/

    private static function verificarVida($indice): bool

    {

        if (isset(self::$equipoDeHeroes[$indice]) && self::$equipoDeHeroes[$indice] instanceof Personaje) {
            if (self::$equipoDeHeroes[$indice]->getVida() <= 0) {
                self::eliminarPersonaje($indice);
                return true;
            } else if (self::$equipoDeHeroes[$indice]->getVida() > 0) {
                return false;
            }
        } else if (isset(self::$monstruosPorRonda[$indice]) && self::$monstruosPorRonda[$indice] instanceof Personaje) {
            if (self::$monstruosPorRonda[$indice]->getVida() <= 0) {
                self::eliminarPersonaje($indice);
                return true;
            } else if (self::$monstruosPorRonda[$indice]->getVida() > 0) {

                return false;
            }
        }
    }





    /* 
despues de verificar la vida del personaje si este fue eliminado, 
este metodo lo elimina de su lista.
*/
    private static function eliminarPersonaje($indice): bool
    {
        if (isset(self::$equipoDeHeroes[$indice])) {
            array_splice(self::$equipoDeHeroes, $indice, 1);
        } else if (isset(self::$monstruosPorRonda[$indice])) {
            array_splice(self::$equipoDeHeroes, $indice, 1);
        }

        self::$estado = self::JUGANDO;
        self::cant_personajes();
        return true;
    }

    /*devuelve la cantidad de personajes que quedan en todas las listas, presentando un ejemplo de cómo se estructura la información
para cada jugador.*/

    private static function cant_personajes(): array
    {

        echo "----------------------------------------------\n";
        echo "Al jugadxr le quedan:" . count(self::$equipoDeHeroes) . "heroes \n";

        return self::$heroesPorJugador;
    }
    /* 
este método guarda el registro de cada enfrentamiento, quien 
se enfrentó a quien cuántos puntos de daño hizo y la vida del 
personaje atacado.
*/
    private static function registroCombate($Iatacante, $Iatacado): void
    {

        if (isset(self::$equipoDeHeroes[$Iatacante])) {
            $atacante = self::$equipoDeHeroes[$Iatacante];
        } else if (isset(self::$equipoDeHeroes[$Iatacado])) {
            $atacado = self::$equipoDeHeroes[$Iatacado];

            $vidaOriginal = intval(self::$vidaOriginalH[$Iatacado]);
            $vidaActual = intval(self::$equipoDeHeroes[$Iatacado]->getVida());

            $daño = $vidaOriginal - $vidaActual;
        }
        if (isset(self::$monstruosPorRonda[$Iatacante])) {
            $atacado = self::$monstruosPorRonda[$Iatacante];
        } else if (isset(self::$monstruosPorRonda[$Iatacado])) {
            $atacado = self::$monstruosPorRonda[$Iatacado];
            $vidaOriginal = intval(self::$vidaOriginalM[$Iatacado]);
            $vidaActual = intval(self::$monstruosPorRonda[$Iatacado]->getVida());

            $daño = $vidaOriginal - $vidaActual;
        }

        echo "----------------------------------------------\n";
        echo "En esta enfrentamento lucho: " . $atacante . " contra " . $atacado . "\n"; {


            echo "Los puntos de daño del atacado son: " . $daño . "\n";
        }

        echo "Le quedan:" . $atacado->getVida() . "  puntos de vida\n";
    }

    /*
cambia el estado del juego a TERMINADO si la cantidad de jugadores de 
un equipo es igual a 0
*/
    private static function finalizarJuego(): void
    {
        if (self::$cantHeroesTotal == 0 || count(self::$monstruosPorRonda) == 0) {
            self::$estado = self::TERMINADO;
        }
    }

    /* 
muestra los personajes que tienen todos los jugadores o uno 
en particular con nombre, tipo de personaje, vida, armas, habilidades, 
mana(en el caso que use)*/

    private static function listarPersonajes(): void
    {
        echo "----------------------------------------------";
        foreach (self::$heroesPorJugador as $jugador => $equipoDeHeroes) {
            echo "Personajes de $jugador:\n";
            foreach ($equipoDeHeroes as $heroe) {



                echo $heroe->getNombre() . "\n ";
                echo $heroe->getVida() . "\n ";
                print_r($heroe->getArsenal()) . "\n ";
                print_r($heroe->getHabilidades()) . "\n ";
                print_r($heroe->getDebilidades()) . "\n ";
                if (method_exists($heroe, 'getMana')) {
                    echo $heroe->getMana() . "\n";
                }
            }
        }
    }





    /*
evalúa cual de los equipos es el ganador, para eso debe evaluar 
cuál equipo quedó con al menos un personaje y el otro con 0 personajes 
: Evalúa cuál de los equipos es el ganador, considerando la
cantidad de puntos de cada personaje (1 punto por personaje con vida). Si
todos los equipos fueron eliminados, el ganador es el equipo monstruo. Se
muestra una tabla de puntajes con los jugadores ordenados del primero al
último
*/

    private static function mostrarGanador(): void
    {
        $puntajes = [];
        foreach (self::$heroesPorJugador as $lista_jugadores => $equipoDeHeroes) {
            $puntosEquipo = 0;

            foreach ($equipoDeHeroes as $heroe) {
                $puntosEquipo += $heroe->getVida();
            }


            $puntajes[$lista_jugadores] = $puntosEquipo;
        }


        arsort($puntajes);
        echo "----------------------------------------------\n";

        foreach ($puntajes as $jugador => $puntos) {
            echo "El jugador $jugador obtuvo un puntaje de $puntos.\n";
        }


        $cantHeroes = count(self::$equipoDeHeroes);
        $cantMostruos = count(self::$monstruosPorRonda);
        if ($cantMostruos == 0 && $cantHeroes > 0) {
            echo "¡El equipo de los monstruos ganó!\n";
        }
    }


    /* 
vuelve a reiniciar el juego siempre y cuando no este en 
estado NO_INICIADO
*/
    private static function reiniciar(): void
    {
        if (self::$estado != self::NO_INICIADO)
            self::$estado = self::REINICIAR;
    }
    /*Setea el valor del estado a ENPAUSA siempre y cuando el juego
esté en JUGANDO.*/
    private static function enPausa()
    {
        if (self::$estado == self::JUGANDO) {
            self::$estado = self::ENPAUSA;
        }
    }
    /* 
vuelve a reiniciar el juego siempre y cuando no este en 
estado NO_INICIADO
*/
    private static function juegoNuevo(): void
    {
        if (self::$estado == self::JUGANDO) {
            self::iniciar();
        }
    }
}
$juegoNuevo = new Juego();
$juegoNuevo->iniciar();
