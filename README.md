# **sql**

***The async class to make it very easy to deal with database connections.***

This is an version of ezsql with **async** capability. In order to use the _native async_ feature of **mysql** or **postgresql**,
there needs to be an way to do other things while waiting for the database to be ready to process you request.

This package takes advantage of `yield` to order to produce coroutines.
If you have no formulary with coroutines or generators, will need to get up to speed before even thinking about using this package.

For starters check out [David Beazley: Generators: The Final Frontier - PyCon 2014](https://youtu.be/D1twn9kLmYg) __video__ 3:50:54, the concepts and the internals of the talk is what's taking place here.

Then after, read the [readme](https://symplely.github.io/coroutine/) of [symmplely/coroutine](https://github.com/symplely/coroutine) package and the [examples](https://github.com/symplely/coroutine/tree/master/examples) to get the feel and power of what is possible.

For an full overview of **ezsql**, see [documentation Wiki](https://github.com/ezSQL/ezsql/wiki/Documentation), which is not completely finish.

