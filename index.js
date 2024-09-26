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

// Pendiente
// function haAprobadoCurso(calificaciones, examenesReprobados, faltas){
//     const { matematicas, ciencias, lengua, historia, arte} = calificaciones
//     let promedioMinimo = 70;
//     let promedioCursos = (matematicas  + ciencias + lengua + historia) / 4 ;
    
//     if(examenesReprobados && faltas){
//         return 'Reprobado';
//     }else if (!examenesReprobados || faltas <= 2){
//         promedioMinimo = 60;
//     }else if (promedioCursos >= 90 ){
//         return arte = 100;
//     }

//     promedioMinimo ? 'Aprobado' : 'Reprobado';
// }

//  --------------- Clase Encadenamiento Opcional ---------------- // 

// const person = {
//     name: 'Juan Pablo',
//     address:  null
// }

// const city = person.address?.city; // ? Se trata de una manera de ejecutar algo con undefined si es nula

// console.log(city);

// --------------- Bucles: FOR, WHILE, DO-WHILE ------------------ //

// FOR 
// for(i = 0; i < 5; i++){
//     console.log(i);
// }

// const array = [10, 20, 30, 40, 50];

// for(i = 0; i < array.length; i++){
//     console.log(array[i]);
// }


// WHILE 
// let count = 0;

// while(count <= 10){
//     console.log(count);
//     count++
// }


// DO-WHILE 

// let count = 0;

// do{
//     console.log(count);
//     count++
// }while(count < 10);

// Funcion para calcular la suma de los numeros del 1 al N usando un bucle 'for'

// function sumaConFor(N){
//     let suma = 0;

//     for(i = 0; i <= N; i++){
//         suma += i;
//     }

//     return suma;
// }

// function sumaConWhile(N){
//     let suma = 0;

//     let increment = 0;

//     while(increment <= N){
//         suma += increment;
//         increment++;

//     }
//     return suma;
// }

// function sumaConDoWhile(N){
//     let suma  = 0;
//     let increment = 0;

//     do {
//         suma += increment;
//         increment++;
//     }while(increment <= N)

//     return suma;
// }

// ------------------- Instrucciones de Salto: break y continue ------------------- // 

// for(i = 0; i < 5; i++){
//     if(i === 3){
//         break; // Detiene el bucle segun la condicion que puse arriba
//     }
//     console.log(i);
// }

// for(i = 0; i < 5; i++){
//     if(i === 3){
//         continue; // Salta del bucle segun la condicion que puse arriba
//     }
//     console.log(i);
// }

// function encontrarNumeroImpar(arr){
//     let numeroImpar = -1;
//     let ejecucionesDeBucle = 0;

//     for(i = 0; arr.length; i++){
//         ejecucionesDeBucle += 1;
//         if(arr[i] % 2 === 0){
//             continue; // Salta de la iteracion porque encontro un numero par
//         }else{
//             numeroImpar = arr[i];
//             break; // Detiene el bucle porque encontro un numero impar
//         }

//     }

//     return {numeroImpar, ejecucionesDeBucle}


// } 

// ---------------- Declaracion y llamada de funciones ------------------- //

// function sumar (a, b){
//     return a + b;
// }

// function restar(a,b){
//     return a - b;
// }

// function multiplicar(a, b){
//     return a * b;
// }

// function dividir(a, b){
//     return a / b;
// }

// function saludar(){
//     console.log('Hola');
// }

// console.log(sumar(1,2));
// saludar();

// function obtenerFechaActual(){
//     const fecha = new Date();
//     const dia = fecha.getDay();
//     const mes = fecha.getMonth();
//     const anio = fecha.getFullYear();

//     return `${dia}/${mes}/${anio}`;
// }

// function obtenerSaludo(){
//     const saludos = ['Hola', 'Hello', 'Ciao', 'Bonjour'];
//     const indice = Math.floor(Math.random() * saludar.length);
//     return saludos[indice];
// }

// function obtenerMensajeMotivador(){
//     const mensajes = ['Nunca te rindas', 'Nunca dejes de creer en ti mismo', 'Traza la linea de éxito'];
//     const indice = Math.floor(Math.random() * mensajes.length);
//     return mensajes[indice];
// }

// obtenerMensajeMotivador();
// obtenerFechaActual();
// obtenerSaludo();

// ------------------ Ámbitos de las Variables (SCOPE)--------------- // 

// const varGlobal = 'Soy una variable global'; // Ambito global

// function saludarAmbito(){
//     let variableLocal = 'Soy una variable local' // Ambito en Funcion
//     if(variableLocal !== ''){
//         varBloque = 'Soy una variable de bloque' // Ambito de bloque
//         varBloque2 = 'Soy otra variable de bloque' //  Ambito de bloque
//     }
//     console.log(varBloque2);
// }

// saludarAmbito();




// ----------------- PARAMETROS Y ARGUMENTOS --------------- //

// function mostrarDomicilio(domicilio){
//     return `${domicilio.calle} ${domicilio.numeroExterior} ${domicilio.codigoPostal} ${domicilio.ciudad} ${domicilio.pais}`;
// }

// const domicilioPrueba = {
//     calle: 'Calle 2',
//     numeroExterior: '123',
//     codigoPostal: '12345',
//     ciudad: 'Guatemala',
//     pais: 'Guatemala'
// };

// mostrarDomicilio('Calle 2');

// function sumar (a, b){
//     return a + b;
// }

// function saludar(nombre){
//     return `Hola ${nombre}`;
// }

// function esPar(numero){
//     return numero % 2 === 0 ? true : false 
// }

// console.log(sumar(10, 20));
// console.log(saludar('Juanito'));
// console.log(esPar(2));

// -------------- Funciones anonimas y funciones de Fecha ------------- //

// function realizarOperacion (a, b, callback){ // Funcion regular 
//     return callback(a, b);
// }

// const sumar = function (a, b){ // Esta es una funcion anonima
//  return a + b; 
// }

// console.log (realizarOperacion(2, 3, sumar));


// // Funciones de Flecha

// const restar = (a, b) => a - b;  // Funncion de Flecha

// const dividir = (a, b) => {
//     return a / b;
// }


// ----------------- SECCION DOM, Document Object Model --------------- //

// Es un modelo estructurado de una pagina web
// Representa los elementos de unna pagina web
// Es dinamico, se puede alterar

// ------------------ CREAR Y MANIPULAR ARRAYS ------------------- //

// const array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// array.push(11); // Agrega un valor al final del arreglo
 
// array.pop(11); // Elimina un valor que esta al final del arreglo

// array.splice(0, 5); // (primerValor, segundoValor) Con primerValor indica en donde inicia y en segundoValor indica cual valor se elimina

// function buscarNumero(numero){
//     return array.filter((e) => e === numero);
// }


// // console.log(buscarNumero(9));
// console.log(array);


// const tareas = [];

// function agregarTareas(nuevaTarea){
//     tareas.push(nuevaTarea);
//     return tareas;
// }

// function eliminarTarea(indice){
//     if(indice >= 0 && indice < tareas.length){
//         tareas.splice(indice, 1);
//     }
//     return tareas;
// }

// function eliminarUltimaTarea(){
//     tareas.pop();
//     return tareas;
// }

// function buscarTarea(contenido){
//     return tareas.filter(tarea => tarea.includes(contenido));

// }


// console.log(agregarTareas('Ir al supermercado'));
// console.log(eliminarTarea(1));
// console.log(eliminarUltimaTarea());
// console.log(buscarTarea('Ir al supermercado'));

// console.log(tareas);

// -------------------- METODOS DE ARRAYS: map, reduce, for-each ------------- //

// const array = [1,2,3,4,5,6,7,8,9,10];

// // N tiene N valores de retorno
// const arrayDuplicate = array.map((x) => x * 2);

// // N tiene 1 valor de retorno
// const arrayincrement = array.reduce((previo, actual) => previo + actual, 0);
// // previo = 0, actual = 1 
// // previo = 1, actual = 2
// // previo = 3, actual = 3
// // previo = 6, actual = 4


// // N tiene 0 valores de retorno  
// array.forEach((x, i) => {
//     array[i] = array[i] * 2
// });


// console.log(array.some(x => x > 0));
// console.log(array.every(x => x > 0));

// console.log(array, arrayincrement);


// const numeros = [1,2,3,4,5,6,7,8,9,10]; 

// // const numerosDuplicados = numeros.map((x) => x * 2);

// // const numerosPares = numeros.filter((x) => x % 2 === 0);

// // const numerosSumar = numeros.reduce((x, y) => x + y, 0);

// // const todosNumeros = numeros.forEach((x, i) => {
// //     numeros[i] = numeros[i] * 2
// // })

// // const numerosEliminados = [...numeros];
// // numerosEliminados.splice(4, 3);

// // const agregarNumero = [...numeros];
// // agregarNumero.push(11);

// // const eliminarNumeroFinal = [...numeros];
// // eliminarNumeroFinal.pop();

// // const palabra = 'HOLA';
// // const partirPalabra = palabra.split('');


// // const palabras = ['Buenas', 'Tardes'];
// // const unirPalabras =  palabras.join(' ');
// // console.log(unirPalabras);

// const numeroTres = numeros.includes(3);

// console.log(numeroTres)

// const array = [
//     [1,2,3],
//     [4,5,6],
//     [7,8,9]  
// ];

// array.forEach((elemento, indice) => {
//     elemento.forEach((elemento2, indice2) => {
//         if(elemento2 % 2 === 0){
//             array[indice][indice2] = elemento2 * 2;
//         }
//     })
// })

// console.log(array[0][2]);

// console.log(array)

// function encontrarCalificainMasAlta(calificacionPorAsignatura){
//     const calificacionMasAlta = [];

//     for(let i = 0; i < calificacionPorAsignatura.length; i++){
//         let max = -1;
//         for(let j = 0; j < calificacionPorAsignatura.length; j++){
//             if(calificacionPorAsignatura[j][i] > max){
//                 max = calificacionPorAsignatura[j][i];
//             }
//         }
//         calificacionMasAlta.push(max);
//     }

//     return calificacionMasAlta
// }

// -------------------- CREACION Y MANIPULACION DE OBJETOS EN JS ------------------- //

// const objeto = {
//     nombre: 'Juan Pablo',
//     edad: 18,
//     estadoActivo: true,
//     calificaciones: {
//         lenguaje: 6,
//         historia: 9,
//         programacion: 10    
//     },
//     promediarCalificaciones: function(){
//         return (this.calificaciones.lenguaje + this.calificaciones.historia + this.calificaciones.programacion) / 3;
//     }
// }

// console.log(objeto.promediarCalificaciones());

// const alumno = {
//     nombre: 'Mario',
//     apellido: 'Pedroza',
//     grado: '3ero',
//     faltas: 5,
//     calificaciones: {
//         matematicas: 9,
//         historia: 3
//     },
//     tutores: [
//         {
//             nombre: 'Pedro',
//             apellido: 'Pedroza',
//             telefono: '555-321-345'
//         },
//         {
//             nombre: 'Maria',
//             apellido: 'Pedroza',
//             telefono: '555-123-532'
//         }
//     ],
//     hobbies: [
//         'Codear',
//         'Bailar',
//         'Cocinar'
//     ],
//     estadoActivo: true
// }

// function mostrarDatosAlumno(){
//     return JSON.stringify(alumno);
// }

// alumno.faltas = 10;

// console.log(mostrarDatosAlumno());


// const estudiante = {
//     nombre: 'Juan Pablo',
//     activo: true,
//     edad: 20,
//     calificaciones: [90,85,78,95,88],
//     calcularPromedio: function(){
//         return this.calificaciones.reduce((x, y) => x + y, 0) / this.calificaciones.length;
//     },
//     obtenerDescripcion: function(){
//         return this.activo ? `Nombre: ${this.nombre}, Edad: ${this.edad}, Promedio: ${this.objeto.calcularPromedio()}` : 'Estudiante inactivo';
//     }
// }

// function mostrarAlumno(){
//     return JSON.stringify(estudiante);
// }

// console.log(mostrarAlumno())

// ------------------ SPREAD OPERATOR Y DESTRUCCION --------------------- //

// const array1 = [1,2,3,4,5];
// const array2 = [6,7,8,9,10];

// const estudiante = {
//     calificaciones: [10,9,8,7,5],
//     faltas: 5,
//     nombre: 'Juan',
// }

// const persona = {
//     nombre: 'Sebastian',
//     edad: 25,
//     apellido: 'Perez'
// }

// const estudiantePersona = {
//     ...estudiante,
//     ...persona,
//     hibrido: true
// }

// function presentarAlumno(estudiante){
//     const {nombre, apellido, edad, } = estudiante; 
//     console.log(`Hola soy ${nombre} ${apellido} y tengo ${edad} anios`);
// }

// presentarAlumno(estudiantePersona);

// const frutas = ['manzana', 'pera', 'uva'];

// frutas.forEach(fruta => console.log(fruta));

// const numeros = [1, 2, 3];
// let suma = 0

// numeros.forEach(numero => suma += numero);
// console.log(suma)

// const numeros = [1,2,3,4,5];

// numeros.forEach(numero => console.log(numero))

// const numeros = [3, 1, 4, 2, 5];

// let numerosOrdenados = numeros.sort((x, y) => x - y);

// console.log(numerosOrdenados)

// const nums = [-2,4,7,8,10];

// let IsNegative = nums.some(num => num > 0);

// console.log(IsNegative);


// Imprimir el array de numeros usando ForEach
// const numeros = [1,2,3,4,5];

// numeros.forEach(numero => console.log(numero));

// Sumar todos los elementos del array usando ForEach
// const numeros = [10,20,30,40,50];
// let suma = 0;

// numeros.forEach(numero => suma += numero);

// console.log(suma)


// Convertir un texto a mayusculas dentro de un Array.

// const nombres = ['ana', 'jorge', 'juan'];

// nombres.forEach(nombre => console.log(nombre.toUpperCase()));

// -------------------- PROMESAS EN JAVASCRIPT ----------------- //

// function operacionAsincrona (){
//     return new Promise((resolve, reject) => {
//         setTimeout(() => {
//             const res = true;
//             if(res){
//                 resolve('Promise Succesfully')
//             }else {
//                 reject('Bad Promise');
//             }
//         }, 1000) // tiempo de espera para ejecutar la promesa
//     })
// }

// console.log('Start');

// operacionAsincrona()
//     .then((result) => console.log(result))
//     .catch((error) => console.log(error))
//     .finally(() => console.log('Exit'))

// console.log('Final');


// --------------------- ASYNC / AWAIT --------------------- //

// function hacerAlgoAsincrono(){
//     return new Promise((resolve, reject) => {
//         setTimeout(() => {
//             const res = true;
//             if(res){
//                 resolve('Promise sucessfully');
//             }else {
//                 reject('Bad Promise');
//             }
//         }, 2000) 
//     });
// }

// async function ejecutarTareaAsincrona(){
//     try{
//         console.log('Initializing');
//         let result = await hacerAlgoAsincrono();
//         console.log(result);
//     }catch(error){
//         console.log(error);
//     }finally{
//         console.log('Exit...');
//     }
// }

// console.log('3');
// ejecutarTareaAsincrona()
// console.log('4')

// --------------------- INTRODUCCION A FETCH -------------------- //

// fetch ('https://jsonplaceholder.typicode.com/todos/1')
//     .then(response => response.json())
//     .then(data => console.log(data))
//     .catch(error => console.log(error))

// const dataToSend = {
//     title: 'foo',
//     body: 'bar',
//     userId: 1
// }

// fetch('https://jsonplaceholder.typicode.com/posts', {
//     method: 'POST',
//     body: JSON.stringify(dataToSend),
//     headers:{
//         'Content-Type': 'application/json'
//         }
// })
// .then(response => response.json())
// .then(data => console.log(data))
// .catch(error => console.error(error))

// // GET -> Extraer informacion
// // POST -> Publicar informacion
// // PUT -> Modificar informacion existente
// // DELETE  -> Eliminar ìnformacion existente