Tasks in Quack Compiler

Sprint 1:

- [x] Remove blueprints
- [x] Remove members
- [x] Short method syntax
- [x] Classes over traits
- [x] Shapes over structs
- [ ] Object initializer with maps
- [x] Parenthesis on function calls and definitions
- [x] Impl with implicit `fn'
- [x] Change syntax for comments
- [x] Isolate declarations from simple statements
- [x] New syntax for lambda-expr

Sprint 2:

- [x] Run typechecker on TryStmt
- [ ] Traverse AST first time to get declarations and bind to scope
- [x] Run typechecker on ContinueStmt
- [x] Run typechecker on BreakStmt
- [ ] Implement parser for type signatures
- [ ] Implement sum-types and type combinators
- [x] Run typechecker on SwitchStmt and CaseStmt // partially done: check TODO inside SwitchStmt.php
- [x] Run typechecker on ElifStmt
- [ ] Run and implement type checker and reasoning for FnStmt
- [x] Run typechecker on ForeachStmt
- [x] Run typechecker on LabelStmt
- [x] Run typechecker on ForStmt
- [ ] Create default class TypeError and replace some of ScopeError that are specific for types
- [ ] Record, in the AST, the positions of the symbols, in order to give better error messages
- [ ] Run typechecker and see type rules for bluerprints, traits, structs and impls
- [ ] Run typechecker for MemberStmt
- [ ] Implement supertype and subtype structural (and nominal) comparators [Duck typing]
- [ ] Skip typechecker over ModuleStmt and OpenStmt
- [ ] Run typechecker for RaiseStmt, ensure derivation from \Exception
- [ ] Assert the context of ReturnStmt and pass the expected return type to it when inside functions
- [ ] Implement subtyping for arrays and maps (check implementation of sum-types)
- [x] Inject type for variable in ForStmt
- [x] Inject type for variable in ForeachStmt
