# Model
This package provides Laravel-like models detached from the framework. The `illuminate/contracts` package is required purely to provide support for `Arrayable` and `Jsonable`.

This package seeks to implement some methods that Laravel utilizes and other packages such as `[jenssegers/model]`(https://github.com/jenssegers/model) leave out and also decouple entirely from the `illuminate/support` package. Those other packages are also proficient if you do not need this.

## To Install
`composer require anteris-dev/model`

## Features
- Accessors and mutators
- Model to Array and JSON conversion
- Hidden attributes in Array/JSON conversion
- Guarded and fillable attributes

## Roadmap
- Appending accessors and mutators to Array/JSON conversion
- Attribute Casting
