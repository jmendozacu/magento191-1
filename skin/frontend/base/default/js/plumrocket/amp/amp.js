/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

AMP = function()
{
	var $this = this;
	var ampTag = 'amp=1';

	$this.url = window.location;

	$this.getAmpUrl = function(url)
	{
		if (!url) url = $this.url;

		if (url.indexOf(ampTag) == -1) {
			url += (url.indexOf('?') == -1) ? '?' : '&';
			url += ampTag;
		}
		return url;
	}

	$this.redirectToAmp = function(url)
	{
		var ampUrl = $this.getAmpUrl(url)
		window.location = ampUrl;
	}

}