// --- Tipos de variables 

// Una variable se utiliza para almacenar datos, asignarle un valor
// Una variable es un espacio en memoria

// var -> Es una forma antigua de declarar una variable
// let -> Es la forma de declarar una variable que puede ser modificado a lo largo del codigo
// const  -> Const en una variable constante NO cambia en el transcurso del codigo


// ¿Como declarar una variable?

// let var1; // Ya la declaré, más no le asigné un valor a esta variable. En este caso esta variable es undefined
// var1 = 1; // Esto es una asignacion de variable 
// let var2 = 2; // Inicializacion de una variable

// const const1 = 3; // Declarando e inicialiando  
// console.log(const1)







// -------------------- Clase Tipos de Datos primitivos ------------------ // 

// En JavaScript existen 5 tipos de datos primitivos

// Tipo de dato int
// const number = 4;

// // Tipo de dato String
// const string = 'Hola';

// // Tipo de dato boolean (true, false)
// const boolean =  true;

// // Tipo de dato undefined -> variables NO inicializados 
// const undefined1 = undefined;

// // Tipo de dato nulo -> Representa la ausencia de algun valor. "NO EXISTE en este momento"
// const nulo = null;

// // Mostrar las variables

// console.log(number);   
// console.log(string);
// console.log(boolean);
// console.log(nulo);

// // String Interpolation
// const string2 = `Hola! usuario numero: ${number}`;

// console.log(string2);






// -------------------- Clase Tipos de Datos de Referencia ------------------ // 

// Object
// array
// function

// const person = {
//     name: 'Juan Pablo',
//     surname: 'Yaxon Taquira',
//     age: 18,
//     address: {
//         street: 'Quinta',
//         number: 7
//     },

//     me: function(){
//         console.log(`Hi, i am ${this.name} ${this.surname}`);
//     }
// }

// // ¿Como acceder a las propiedades de un Objeto?

// // console.log(person.address.number);

// // person.me();

// // ------------ Array en JS ------------- // 

// let person2 = person;

// person2.name = 'Carlos Garcia';

// console.log(person, person2);

// // const array = ['gato', 2, 3, 4, 5];

// // console.log(array[0]);



// --------------- Clase Conversion de Tipos de Datos -------------------- //

// const num = 32;

// const text = String(num);

// const text2 = num.toString();

// const num2 = Number(text);

// const num3 = Number(true);

// const num4 = parseInt('32.51');

// const num5 = parseFloat('42.5');

// console.log(num2, num3, num4, num5);

// --------------- Clase Hoisting ----------------------- //

// console.log(sumar(10, 5));

// function sumar(a, b){
//     return a + b;
// }

// var // si tiene hoisting

// const y let // no se elevan

// function // se eleva toda la funcion 






// ----------------- LECCION 43 CONTROLES DE FLUJO --------------------- // 

// const num = 5;

// if(num > 0 && num < 50){
//     console.log('El numero es mayor que 0 y menor que 50');
//     if (num === 5){
//         console.log('El numero es 5');
//     }
// }else {
//     console.log('El numero no es valido');
// }

// const edad = 18;

// if(edad >= 18){
//     console.log('Puedes Votar');
// } else if (edad <= 0 || edad > 120){
//     console.log('Edad incorrecta');
// }else {
//     console.log('No puedes votar');
// }

// const color = 'verde';

// switch(color){
//     case 'amarillo':
//         console.log('El color es amarillo');
//         break;
//     case 'rojo':
//         console.log('El color es rojo');
//         break;
//     default:
//         console.log('El color no es ni amarillo ni rojo');
//         break;
// }


// -------- Prueba de Controles de Flujo ------- // 

// function determinarNivelRendimiento(calificacion){
//     if(calificacion >= 90){
//         return 'Excelente';
//     }
//     else if(calificacion >= 70 && calificacion < 90){
//         return 'Bueno';
//     }
//     else if(calificacion >= 50 && calificacion < 70){
//         return 'Regular';
//     }
//     else if(calificacion < 50){
//         return 'Deficiente';
//     }
// }

// function obtenerMensajeDeNivel(nivel){
//     switch(nivel){
//         case 'Excelente':
//             console.log('¡Muy bien! Tu rendimiento es excelente.');
//             break;
//         case 'Bueno':
//             console.log('Buen trabajo. Tu rendimiento es bueno.');
//             break;
//         case 'Regular':
//             console.log('Necesitas mejorar. Tu rendimiento es regular.');
//             break;
//         case 'Deficiente':
//             console.log('Necesitas estudiar más. Tu rendimiento es deficiente.');
//             break;
//         default: 
//             console.log('Nivel de rendimiento no válido. ');
//             break;
//     }
// }


// -------------- Operadores Logicos Nullish Coalescing y ternarios ---------------- // 

// const estatus = 'Pendiente';

// const pagado = true;

// if(estatus === 'Completado' && pagado === true){ // Ambas expresiones deben ser verdaderas o falsas
//     console.log('El pedido esta completado y pagado');
// }else if (estatus === null || pagado === null){ // Cualquiera de las dos expresiones es verdadera 
//     console.log('El pedido no se ha creado');
// }else if (estatus !== null && pagado !== null){
//     console.log('El pedido es pendiente de pago');
// }

// // AND &&, OR ||, NOT !


// Nullish Coalescing ??
// El operador Nullish Coalescing devuelve el valor derecho cuando el izquierdo es null o undefined

// const nombre = 'Juan Pablo Angel Yaxon Taquira';
// const nombreDefecto = 'Invitado';

// const nombreUsuario = nombre ?? nombreDefecto;

// console.log(nombreUsuario);

// // Operador ternario

// // ternario (condicion ? true : false)

// const hora = 10;
// const saludo = hora < 12 ? 'Buenos dias' : 'Buenas tardes';

// console.log(saludo);


// // Operadores Logicos
//     // &&, ||, !
// // Nullish Coalescing
//     // ?? (null or undefined)
// // Ternario 
//     // (condicion ? true : false)

function haAprobadoCurso(calificaciones, examenesReprobados, faltas){
    const calificaciones = { 
        matematicas: 0,
        ciencias: 0,
        lengua: 0,
        historia: 0,
        arte: 0
    }
    let promedioMinimo = 70;
    let promedioCursos = (matematica + ciencias + lengua + historia) / 4 ;
    
    if(examenesReprobados && faltas){
        return 'Reprobado';
    }else if (!examenesReprobados || faltas <= 2){
        promedioMinimo = 60;
    }else if (promedioCursos >= 90 ){
        return arte = 100;
    }

    promedioMinimo ? 'Aprobado' : 'Reprobado';
}