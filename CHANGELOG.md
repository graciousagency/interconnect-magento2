**1.0.0** : 2017/10/13
- Initial release.

**1.0.1** : 2017/10/16
- Delegated order item data formatting to separate factory class.
- Disabled quote event listener (next sprint feature).
- Added README.md.
- Changed logging.
- Removed ModelInspector class (won't check whether model is new or not anymore).
- Changed some property names in factories that didn't match the ones in the Interconnect web service.

**1.1.0** : 2017/10/17
- Added api key input for backend store configuration and implemented this in the REST client.

**1.1.1** : 2017/10/18
- Fixed problem with events firing multiple times by sending data from controller overrides and listening to different
events.
- Added custom logger for notice messages so they don't end up with all the other logs lines.

**1.2.0** : 2017/10/18
- Delegated address data formatting to separate factory class.
- Delegated sending order to a trait to prevent repeating code in console command and event observer.
- Added customer data to order (specifically useful for anonymous orders that weren't preceded by a customer registration).

**1.3.0** : 2017/10/?
- Included extra data required for the Robin api.
- Only log certain things in developer mode.
- Added missing return statements in console commands for aborting.
- Included app data in the sent headers.
- Custom Exception classes.
- Cleaned up logging (debug logging only in developer mode + verbose exception logging).
- Fixed problem in Http Client with double '/' in urls.

**1.4.0** : 2017/10/30
- Refactored some class names ('reflection') and implementation of those classes to something more appropriate.