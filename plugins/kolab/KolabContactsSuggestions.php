<?php

class KolabContactsSuggestions implements \RainLoop\Providers\Suggestions\ISuggestions
{
	// TODO: make setting
	public $sFolderName = 'Contacts';

	public function Process(\RainLoop\Model\Account $oAccount, string $sQuery, int $iLimit = 20): array
	{
		$sQuery = \trim($sQuery);
		if (2 > \strlen($sQuery)) {
			return [];
		}

		$oActions = \RainLoop\Api::Actions();
		$oMailClient = $oActions->MailClient();
		if (!$oMailClient->IsLoggined()) {
			$oAccount = $oActions->getAccountFromToken();
			$oAccount->IncConnectAndLoginHelper($oActions->Plugins(), $oMailClient, $oActions->Config());
		}
		$oImapClient = $oMailClient->ImapClient();

		$metadata = $oImapClient->FolderGetMetadata($this->sFolderName, [\MailSo\Imap\Enumerations\MetadataKeys::KOLAB_CTYPE]);
		if ($metadata && 'contact' !== \array_shift($metadata)) {
			// Throw error
//			$oImapClient->FolderList() : array
			return [];
		}
		$oImapClient->FolderSelect($this->sFolderName);

		$sQuery = \MailSo\Imap\SearchCriterias::escapeSearchString($oImapClient, $sQuery);
		$aUids = \array_slice(
			$oImapClient->MessageSimpleSearch("FROM {$sQuery}"),
			0, $iLimit
		);

		$aResult = [];
		foreach ($oImapClient->Fetch(['BODY.PEEK[HEADER.FIELDS (FROM)]'], \implode(',', $aUids), true) as $oFetchResponse) {
			$oHeaders = new \MailSo\Mime\HeaderCollection($oFetchResponse->GetHeaderFieldsValue());
			$oFrom = $oHeaders->GetAsEmailCollection(\MailSo\Mime\Enumerations\Header::FROM_, true);
			foreach ($oFrom as $oMail) {
				$aResult[] = [$oMail->GetEmail(), $oMail->GetDisplayName()];
			}
		}

		return $aResult;
	}
}
