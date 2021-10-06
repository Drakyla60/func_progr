// const a = 5;
//
// function b(c) {
//   return c + a;
// }
//
// console.log(b(3));
//
// console.info('-----');

// function d() {
//   const a = 3;
//
//   function b(c) {
//     return c + a;
//   }
//
//   console.log(b(3));
// }
//
// d();
//
// console.info('-----');

// function e(d) {
//
//   function f(e) {
//     return e + d;
//   }
//
//   console.log(f(3));
// }
//
// e(4);
//
// console.info('-----');

// function q(d) {
//
//     const w = function (e) {
//         return e + d;
//     }
//
//     return w(8);
// }
//
// console.log(q(4));
//
// console.info('-----');

function d1(e) {
    return function (g) {
        return g + e;
    }
}

const p1 = d1(6);
console.log(p1(15));
console.log(p1(18));

const p2 = d1(60);
console.log(p2(15));
console.log(p2(18));


console.info('-----');

