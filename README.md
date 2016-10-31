# RSFeedReader
A script to read RealSatisfied feeds and normalise the data for website publishing

Generic PHP script to read RealSatisfied feeds, normalise the data and make available in an array for generic processing and display on a website.
 * ACCEPTS TWO ARGUMENTS
 * >> vanity_key : the vanity_key of the agent that the feed relates to (required). Always use agent vanity_key, even for offices.
 * >> feed_type : the type of feed (V1|V2|OFFICE|HAR|RDC). Where not specified V2 is assumed. (optional)
 * Returns a $data array ( 
			 - $data Array contains all data related to this feed.
			 - $ratings Array contains name/val pairs for feed specific ratings 
			 - $items Array contains the transaction specific information including ratings where available
			 - use the names from the ratings array to reference the ratings in the $items array
			 - all feeds contain a $data["summaryrating"] where ratings are available.
			 - review the RealSatisfied XML name space for detail on specific data available : http://rss.realsatisfied.com/ns/realsatisfied/
			 )
 * This script is provided under the MIT licence. This licence does not alter or impact any rights under the RealSatisfied terms of service or under any service contract entered into with RealSatisifed or it's parent company Placester Inc.
 
  Version 1.2
 * Last updated 24-Oct-2016 by Phil Kells
 
Change Log

++ 0.1 
 * Initial release

++ 0.2 
 * Altered support for office vanity keys to only use agent vanity keys 
 * Added display name and agent avatar to the $ratings array for office feed
 
++ 0.3
 * Split into a class and exmaple structure

++ 1.0
 * Initial release
 
++ 1.1 
 * Altered support for office vanity keys to only use agent vanity keys 
 * Added display name and agent avatar to the $ratings array for office feed
 
++ 1.11
 * Added feedsource description as a variable

++ 1.12
 * fixed typo

++ 1.3
 * tweaked RD and errors
