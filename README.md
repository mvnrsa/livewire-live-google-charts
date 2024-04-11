# Live Google Charts for Laravel Livewire 3 (Auto refresh/poll)


## Live?
**Live** as in the charts will auto refresh at a specified interval using the **Livewire wire:poll** attribute

Note that the component is only drawn the first time and thereafter only the data is updated on every poll, so the data used for polling is siqnificantly less and the chart is just **updated, not recreated** every time.

## Credit
This package is an extension of the excellent [Helvetitec/lagoon-charts](https://github.com/Helvetitec/lagoon-charts) Google charts package by [Helvetitec](https://github.com/Helvetitec).  (Except these ones are "live" :-)


### Author

[Marnus van Niekerk](https://github.com/mvnrsa) - [mvnrsa](https://github.com/mvnrsa) - [laravel@mjvn.net](mailto:laravel@mjvn.net)