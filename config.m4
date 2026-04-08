PHP_ARG_ENABLE([deepclone],
  [whether to enable deepclone support],
  [AS_HELP_STRING([--enable-deepclone],
    [Enable deepclone support])],
  [no])

if test "$PHP_DEEPCLONE" != "no"; then
  PHP_NEW_EXTENSION(deepclone, deepclone.c, $ext_shared)
fi
