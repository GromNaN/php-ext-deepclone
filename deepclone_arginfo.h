/* This is a generated file, edit deepclone.stub.php instead.
 * Stub hash: e23b7774f3b4c5b282b073d71a10d66618059023 */

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_deepclone_to_array, 0, 1, IS_ARRAY, 0)
	ZEND_ARG_TYPE_INFO(0, value, IS_MIXED, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_deepclone_from_array, 0, 1, IS_MIXED, 0)
	ZEND_ARG_TYPE_INFO(0, data, IS_ARRAY, 0)
ZEND_END_ARG_INFO()

ZEND_FUNCTION(deepclone_to_array);
ZEND_FUNCTION(deepclone_from_array);

static const zend_function_entry ext_functions[] = {
	ZEND_FE(deepclone_to_array, arginfo_deepclone_to_array)
	ZEND_FE(deepclone_from_array, arginfo_deepclone_from_array)
	ZEND_FE_END
};
