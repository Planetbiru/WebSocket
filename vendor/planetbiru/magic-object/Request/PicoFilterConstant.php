<?php

namespace MagicObject\Request;

class PicoFilterConstant
{
    const NEW_LINE = "\r\n";
    const DATABASE_CONNECTION_IS_NULL = "Database connection is null";
    const APPLICATION_JSON = 'application/json';
    const X_API_KEY = 'X_API_KEY';
    const X_TIMESTAMP = 'X_TIMESTAMP';
    const X_SIGNATURE = 'X_SIGNATURE';
    const X_TELLER_UNIQUE_ID = 'X_TELLER_UNIQUE_ID';
    const X_ADMIN_UNIQUE_ID = 'X_ADMIN_UNIQUE_ID';
    const DAY_TO_SECOND = 86400;
    const DUPLICATED_TRANSACTION = "Duplicated transaction";

	const FILTER_SANITIZE_NO_DOUBLE_SPACE = 512;
	const FILTER_SANITIZE_PASSWORD = 511;
	const FILTER_SANITIZE_ALPHA = 510;
	const FILTER_SANITIZE_ALPHANUMERIC = 509;
	const FILTER_SANITIZE_ALPHANUMERICPUNC = 506;
	const FILTER_SANITIZE_NUMBER_UINT = 508;
	const FILTER_SANITIZE_NUMBER_INT = 519;
	const FILTER_SANITIZE_URL = 518;
	const FILTER_SANITIZE_NUMBER_FLOAT = 520;
	const FILTER_SANITIZE_STRING_NEW = 513;
	const FILTER_SANITIZE_ENCODED = 514;
	const FILTER_SANITIZE_STRING_INLINE = 507;
	const FILTER_SANITIZE_STRING_BASE64 = 505;
	const FILTER_SANITIZE_IP = 504;
	const FILTER_SANITIZE_NUMBER_OCTAL = 503;
	const FILTER_SANITIZE_NUMBER_HEXADECIMAL = 502;
	const FILTER_SANITIZE_COLOR = 501;
	const FILTER_SANITIZE_POINT = 500;
	
	const FILTER_SANITIZE_BOOL = 600;
	const FILTER_VALIDATE_URL = 273;
	const FILTER_VALIDATE_EMAIL = 274;
	const FILTER_SANITIZE_EMAIL = 517;
	const FILTER_SANITIZE_SPECIAL_CHARS = 515;
	const FILTER_SANITIZE_ASCII = 601;
}