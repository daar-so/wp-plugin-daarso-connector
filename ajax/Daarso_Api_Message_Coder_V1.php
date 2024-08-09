<?php

final class Daarso_Api_Message_Coder_V1
{
	private string $originGuid;
	private string $targetGuid;
	private string $openSslKey;

	public function __construct()
	{
		$this->originGuid = get_option(Daarso_Options::REQUEST_MESSAGE_ORIGIN_GUID);
		$this->targetGuid = get_option(Daarso_Options::REQUEST_MESSAGE_TARGET_GUID);
		$this->openSslKey = get_option(Daarso_Options::REQUEST_MESSAGE_OPENSSL_KEY);
	}

	public function decode(string $codedMessage)
	{
		$result = self::sslDecrypt(unserialize(base64_decode($codedMessage)), $decodedMessage, $this->openSslKey);
		if ($result) {
			$resultArray = json_decode($decodedMessage, true);
			if (isset($resultArray['wpmanagerGuidId'], $resultArray['websiteGuidId'], $resultArray['action'], $resultArray['data']) && ($this->originGuid === $resultArray['wpmanagerGuidId']) && ($this->targetGuid === $resultArray['websiteGuidId'])) {
				return ['action' => $resultArray['action'], 'data' => $resultArray['data']];
			}
		}
		return false;
	}

	public function encodeSuccess(array $message): string
	{
		$resultArray['wpmanagerGuidId'] = $this->originGuid;
		$resultArray['websiteGuidId'] = $this->targetGuid;
		$resultArray['message'] = $message;
		$resultArray['success'] = true;
		$resultArray['salt'] = microtime(true);
		$result = self::sslEncrypt(json_encode($resultArray), $codedResponse, $this->openSslKey);
		if ($result) {
			return base64_encode(serialize($codedResponse));
		}
		return false;
	}

	private static function sslEncrypt($source, &$output, $key): bool
	{
		$maxlength = 100;
		$output = [];
		while ($source) {
			$input = substr($source, 0, $maxlength);
			$source = substr($source, $maxlength);
			$result = openssl_public_encrypt($input, $encrypted, $key);
			$output[] = $encrypted;
			if (!$result) {
				$output = [];
				return false;
			}
		}
		return true;
	}

	private static function sslDecrypt($source, &$output, $key): bool
	{
		$maxlength = 100;
		$output = '';
		foreach ($source as $row) {
			$result = openssl_public_decrypt($row, $decrypted, $key);
			$output .= $decrypted;
			if (!$result) {
				$output = '';
				return false;
			}
		}
		return true;
	}
}
