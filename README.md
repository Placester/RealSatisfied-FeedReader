# RSFeedReader
A script to read RealSatisfied feeds and normalise the data for website publishing

Generic PHP script to read RealSatisfied feeds, normalise the data and make available in an array for generic processing and display on a website.
 * ACCEPTS TWO ARGUMENTS
 * >> vanity_key : the vanity_key of the agent that the feed relates to (required). Always use agent vanity_key, even for offices.
 * >> feed_type : the type of feed (V1|V2|OFFICE|HAR|RDC). Where not specified V2 is assumed. (optional)
 * 
 * This script is provided under the MIT licence below. 
 * This licence does not alter or impact any rights under the RealSatisfied terms of service or under any 
 * service contract entered into with RealSatisifed or it's parent company Placester Inc.
 
  Version 0.3
 * Last updated 27-Aug-2016 by Phil Kells
 
 Change Log
++ 0.1 
 * Initial release
 
++ 0.2 
 * Altered support for office vanity keys to only use agent vanity keys 
 * Added display name and agent avatar to the $ratings array for office feed
 
 ++ 0.3
 * Split into a class and exmaple structure
