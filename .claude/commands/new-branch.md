---
description: Crea un nuevo branch siguiendo Conventional Branch
---

Voy a crear un nuevo branch con la convención del proyecto.

Preguntale al usuario:
1. Qué tipo de branch quiere crear (feature, fix, hotfix, chore, docs, refactor, test)
2. Una descripción corta del propósito

Luego:
- Convertí la descripción a kebab-case (minúsculas, guiones, sin acentos ni caracteres especiales)
- Armá el nombre como: `<tipo>/<descripcion-kebab-case>`
- Mostrá el nombre final al usuario y pedí confirmación
- Si confirma, ejecutá: `git checkout -b <nombre-final>`
- Si no, preguntá qué ajustar

Validá que el tipo esté en la lista permitida antes de crear el branch.