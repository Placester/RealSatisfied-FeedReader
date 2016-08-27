<?
class feedread {
/* 
 * Generic PHP script to read RealSatisfied feeds, normalise the data and make available in an array for generic processing and display on a website.
 * ACCEPTS TWO ARGUMENTS
 * >> vanity_key : the vanity_key of the agent that the feed relates to (required). Always use agent vanity_key, even for offices.
 * >> feed_type : the type of feed (V1|V2|OFFICE|HAR|RDC). Where not specified V2 is assumed. (optional)
 * 
 * This script is provided under the MIT licence below. 
 * This licence does not alter or impact any rights under the RealSatisfied terms of service or under any 
 * service contract entered into with RealSatisifed or it's parent company Placester Inc.
 * 
 * 

MIT License
==============
Copyright (c) 2016 RealSatisfied & Placester Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 * 
 */
	
private $version = "0.3";

	function get_data($vanity_key, $feed_type="V2"){
		try{
			if($vanity_key==''){
				throw new Exception("vanity_key is required");
			}

			$feed_type = strtoupper($feed_type);

			if($feed_type =='V1' || $feed_type =='V2' || $feed_type =='OFFICE' || $feed_type =='RDC' || $feed_type =='HAR'){
				$feed_type = strtoupper($_GET["t"]);
			}else{
				$feed_type = 'V2';
			}

			//initalise variables
			$datapath = $ratingpath = "";

			switch($feed_type){
				case "V1":
					$ratingpath = "http://rss.realsatisfied.com/rss/v1/agent/" . $vanity_key;
					$datapath = "";
					$feedsource = "RealSatisfied V1, Aggregate ratings only";

				break;	
				case "OFFICE":
					$ratingpath = "http://rss.realsatisfied.com/rss/agent/" . $vanity_key;
					$datapath = "";
					$feedsource = "RealSatisfied Office Feed";

				break;
				case "HAR":
					$ratingpath = "http://rss.realsatisfied.com/har/agent/" . $vanity_key;
					$datapath = "http://rss.realsatisfied.com/rss/v1/agent/" . $vanity_key;
					$feedsource = "HAR style ratings (profile data from RealSatisfied)";

				break;
				case "RDC":
					$ratingpath = "http://rss.realsatisfied.com/rdc/agent/" . $vanity_key;
					$datapath = "http://rss.realsatisfied.com/rss/v1/agent/" . $vanity_key;
					$feedsource = "RDC style ratings (profile data from RealSatisfied)";
				break;
				case "V2":
				default:
					$ratingpath = "http://rss.realsatisfied.com/rss/v2/agent/" . $vanity_key;
					$datapath = "";
					$feedsource = "RealSatisfied V2, Variable feed format based on profile settings";
				break;	
			}


			// read feed into SimpleXML object
			$rating_data = simplexml_load_file($ratingpath) or die("<error>Rating Data Source Unavailable</error>");
			if($datapath!=""){
				$profile_data = simplexml_load_file($datapath) or die("<error>Data Source Unavailable</error>");
			}

			switch($feed_type){
				case "V1":

					$data = array();

					$rs = $rating_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');

					$data = array(
						"profile_page"=>$rating_data->channel->link,
						"responsecount"=>$rs->responseCount,
						"name"=>$rs->display_name,
						"agent_name"=>$rs->display_name,
						"phone"=>$rs->phone,
						"mobile"=>$rs->mobile,
						"title"=>$rs->title,
						"licence"=>$rs->licence,
						"team_name"=>$rs->team_name,
						"office"=>$rs->office,
						"office_licence"=>$rs->office_licence,
						"address"=>$rs->address,
						"city"=>$rs->city,
						"state"=>$rs->state,
						"postcode"=>$rs->postcode,
						"website"=>$rs->website,
						"avatar"=>$rs->avatar,
						"logo"=>$rs->logo,
						"officekey"=>$rs->officekey,
						"officefeed"=>$rs->officefeed,
						"responseCount"=>$rs->responseCount,
						"show_scores"=>$rs->show_scores,
						"show_tetimonials"=>$rs->show_testimonials,
						"ratingdisplayformat"=>$rs->ratingdisplayformat,
						"feed_format"=>$rs->feed_format,
						"office_website"=>$rs->website,
						"entity_website"=>$rs->personal_url,
						"facebook"=>$rs->facebook_url,
						"twitter"=>$rs->twitter_url,
						"google"=>$rs->googleplus_url,
						"linkedin"=>$rs->linkedin_url,
						"yelp"=>$rs->yelp_url,
						"rdc"=>$rs->realtor_url,
						"zillow"=>$rs->zillow_url,
						"isrealtor"=>$rs->realtor			
					);

					//ratings
					$ratings = array();
					if($rs->show_scores=="1"){
						$data["summaryrating"] = $rs->overall_satisfaction;
						$ratings[] = array(
							"name"=>"satisfaction", 
							"score"=>$rs->overall_satisfaction
						);
						$ratings[] = array(
							"name"=>"performance", 
							"score"=>$rs->performance_rating
						);
						$ratings[] = array(
							"name"=>"recommendation", 
							"score"=>$rs->recommendation_rating
						);
					}
					$data["ratings"] = $ratings;		


					//create the items array
					$items = array();
					foreach ($rating_data->channel->item as $item){
						$rs_item = $item->children('http://rss.realsatisfied.com/ns/realsatisfied/');	
						//add to array...
						$items[] = array(
							"name"=>$item->title, 
							"content"=>$item->description,
							"customer_type"=>$rs_item->customer_type,
							"date"=>$item->pubDate,
							"id"=>$item->guid
						);
					}
					$data["items"] = $items;


				break;	
				case "OFFICE":

					$data = array();
					//get office feed from agent feed
					$rs = $rating_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');
					$ratingpath = $rs->officefeed;
					//reset paths
					$rating_data = simplexml_load_file($ratingpath) or die("<error>Rating Data Source Unavailable</error>");
					$rs = $rating_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');

					$data = array(
						"profile_page"=>"http://www.realsatisfied.com/office/".$vanity_key,
						"responsecount"=>$rs->responseCount,
						"office_name"=>$rs->office,
						"name"=>$rs->office,
						"phone"=>$rs->phone,
						"office_licence"=>$rs->office_licence,
						"address"=>$rs->address,
						"city"=>$rs->city,
						"state"=>$rs->state,
						"postcode"=>$rs->postcode,
						"website"=>$rs->website,
						"logo"=>$rs->logo,
						"responseCount"=>$rs->responseCount,
						"show_scores"=>$rs->show_scores,
						"ratingdisplayformat"=>$rs->ratingdisplayformat,
						"office_website"=>$rs->website,
						"facebook"=>$rs->facebook_url,
						"twitter"=>$rs->twitter_url,
						"google"=>$rs->googleplus_url,
						"linkedin"=>$rs->linkedin_url
					);

					$ratings = array();
					if($rs->show_scores=="1"){
						$data["summaryrating"] = $rs->overall_satisfaction;
						$ratings[] = array(
							"name"=>"satisfaction", 
							"score"=>$rs->overall_satisfaction
						);
						$ratings[] = array(
							"name"=>"performance", 
							"score"=>$rs->performance_rating
						);
						$ratings[] = array(
							"name"=>"recommendation", 
							"score"=>$rs->recommendation_rating
						);						
					}
					$data["ratings"] = $ratings;

					//create the items array
					$items = array();
					foreach ($rating_data->channel->item as $item){
						$rs_item = $item->children('http://rss.realsatisfied.com/ns/realsatisfied/');	
						//add to array...
						$items[] = array(
							"name"=>$item->title, 
							"content"=>$item->description,
							"customer_type"=>$rs_item->customer_type,
							"display_name"=>$rs_item->display_name,
							"avatar"=>$rs_item->avatar,
							"date"=>$item->pubDate,
							"id"=>$item->guid,
							"ratings"=>null
						);
					}
					$data["items"] = $items;		


				break;
				case "HAR":

					$data = array();

					$rs = $profile_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');

					$data = array(
						"profile_page"=>"http://www.realsatisfied.com/".$vanity_key,
						"responsecount"=>$rs->responseCount,
						"agent_name"=>$rs->display_name,
						"name"=>$rs->display_name,
						"phone"=>$rs->phone,
						"mobile"=>$rs->mobile,
						"title"=>$rs->title,
						"licence"=>$rs->licence,
						"team_name"=>$rs->team_name,
						"office"=>$rs->office,
						"office_licence"=>$rs->office_licence,
						"address"=>$rs->address,
						"city"=>$rs->city,
						"state"=>$rs->state,
						"postcode"=>$rs->postcode,
						"website"=>$rs->website,
						"avatar"=>$rs->avatar,
						"logo"=>$rs->logo,
						"officekey"=>$rs->officekey,
						"officefeed"=>$rs->officefeed,
						"responseCount"=>$rs->responseCount,
						"show_scores"=>"1",
						"show_tetimonials"=>"1",
						"ratingdisplayformat"=>"stars",
						"feed_format"=>"detailed",
						"office_website"=>$rs->website,
						"entity_website"=>$rs->personal_url,
						"facebook"=>$rs->facebook_url,
						"twitter"=>$rs->twitter_url,
						"google"=>$rs->googleplus_url,
						"linkedin"=>$rs->linkedin_url,
						"yelp"=>$rs->yelp_url,
						"rdc"=>$rs->realtor_url,
						"zillow"=>$rs->zillow_url,
						"isrealtor"=>$rs->realtor			
					);

					//HAR ratings

					$ratings = array();
					if($rs->show_scores=="1"){
						$data["summaryrating"] = $rating_data->totalOverallRating;
						$ratings[] = array(
							"name"=>"overallRating", 
							"score"=>$rating_data->totalOverallRating
						);
						$ratings[] = array(
							"name"=>"competency", 
							"score"=>$rating_data->totalCompetency
						);
						$ratings[] = array(
							"name"=>"marketKnowledge", 
							"score"=>$rating_data->totalMarketKnowledge
						);
						$ratings[] = array(
							"name"=>"communication", 
							"score"=>$rating_data->totalCommunication
						);
						$ratings[] = array(
							"name"=>"experience", 
							"score"=>$rating_data->totalExperience
						);			
					}
					$data["ratings"] = $ratings;


					//create the items array
					$items = array();
					foreach ($rating_data->ratingDetail as $item){
						//add to array...
						$items[] = array(
							"customer_type"=>$item->represent,
							"date"=>$item->lastModified,
							"id"=>$item->ratingDetailID,
							"content"=>$item->comments,
							"overallRating"=>$item->overallRating,
							"competency"=>$item->competency,
							"marketKnowledge"=>$item->marketKnowledge,
							"communication"=>$item->communication,
							"experience"=>$item->experience
							);				
					}
					$data["items"] = $items;		

				break;
				case "RDC":

					$data = array();

					$rs = $profile_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');

					$data = array(
						"profile_page"=>"http://www.realsatisfied.com/".$vanity_key,
						"responsecount"=>$rs->responseCount,
						"agent_name"=>$rs->display_name,
						"name"=>$rs->display_name,
						"phone"=>$rs->phone,
						"mobile"=>$rs->mobile,
						"title"=>$rs->title,
						"licence"=>$rs->licence,
						"team_name"=>$rs->team_name,
						"office"=>$rs->office,
						"office_licence"=>$rs->office_licence,
						"address"=>$rs->address,
						"city"=>$rs->city,
						"state"=>$rs->state,
						"postcode"=>$rs->postcode,
						"website"=>$rs->website,
						"avatar"=>$rs->avatar,
						"logo"=>$rs->logo,
						"officekey"=>$rs->officekey,
						"officefeed"=>$rs->officefeed,
						"responseCount"=>$rs->responseCount,
						"show_scores"=>"1",
						"show_tetimonials"=>"1",
						"ratingdisplayformat"=>"stars",
						"feed_format"=>'detailed',
						"office_website"=>$rs->website,
						"entity_website"=>$rs->personal_url,
						"facebook"=>$rs->facebook_url,
						"twitter"=>$rs->twitter_url,
						"google"=>$rs->googleplus_url,
						"linkedin"=>$rs->linkedin_url,
						"yelp"=>$rs->yelp_url,
						"rdc"=>$rs->realtor_url,
						"zillow"=>$rs->zillow_url,
						"isrealtor"=>$rs->realtor			
					);

					//RDC ratings
					$ratings = array();
					if($rs->show_scores=="1"){
						$data["summaryrating"] = $rating_data->totalOverallRating;
						$ratings[] = array(
							"name"=>"overallRating", 
							"score"=>$rating_data->totalOverallRating
						);
						$ratings[] = array(
							"name"=>"responsiveness", 
							"score"=>$rating_data->totalresponsiveness
						);
						$ratings[] = array(
							"name"=>"marketExpertise", 
							"score"=>$rating_data->totalmarketExpertise
						);
						$ratings[] = array(
							"name"=>"negotiationSkills", 
							"score"=>$rating_data->totalnegotiationSkills
						);
						$ratings[] = array(
							"name"=>"professionalismCommunications", 
							"score"=>$rating_data->totalprofessionalismCommunications
						);			
					}
					$data["ratings"] = $ratings;		

					//create the items array
					$items = array();
					foreach ($rating_data->ratingDetail as $item){
						//add to array...
						$items[] = array(
							"customer_type"=>$item->represent,
							"date"=>$item->lastModified,
							"id"=>$item->ratingDetailID,
							"content"=>$item->comments,
							"overallRating"=>$item->overallRating,
							"responsiveness"=>$item->responsiveness,
							"marketExpertise"=>$item->marketExpertise,
							"negotiationSkills"=>$item->negotiationSkills,
							"professionalismCommunications"=>$item->professionalismCommunications
							);				
					}
					$data["items"] = $items;		

				break;
				case "V2":
				default:

					$data = array();

					$rs = $rating_data->channel->children('http://rss.realsatisfied.com/ns/realsatisfied/');
					$data = array(
						"profile_page"=>$rating_data->channel->link,
						"responsecount"=>$rs->responseCount,
						"name"=>$rs->display_name,
						"agent_name"=>$rs->display_name,
						"phone"=>$rs->phone,
						"mobile"=>$rs->mobile,
						"title"=>$rs->title,
						"licence"=>$rs->licence,
						"team_name"=>$rs->team_name,
						"office"=>$rs->office,
						"office_licence"=>$rs->office_licence,
						"address"=>$rs->address,
						"city"=>$rs->city,
						"state"=>$rs->state,
						"postcode"=>$rs->postcode,
						"website"=>$rs->website,
						"avatar"=>$rs->avatar,
						"logo"=>$rs->logo,
						"officekey"=>$rs->officekey,
						"officefeed"=>$rs->officefeed,
						"responseCount"=>$rs->responseCount,
						"show_scores"=>$rs->show_scores,
						"show_tetimonials"=>$rs->show_testimonials,
						"ratingdisplayformat"=>$rs->ratingdisplayformat,
						"feed_format"=>$rs->feed_format,
						"office_website"=>$rs->website,
						"entity_website"=>$rs->personal_url,
						"facebook"=>$rs->facebook_url,
						"twitter"=>$rs->twitter_url,
						"google"=>$rs->googleplus_url,
						"linkedin"=>$rs->linkedin_url,
						"yelp"=>$rs->yelp_url,
						"rdc"=>$rs->realtor_url,
						"zillow"=>$rs->zillow_url,
						"isrealtor"=>$rs->realtor			
					);

					//ratings
					$ratings = array();
					if($rs->show_scores=="1"){
						$data["summaryrating"] = $rs->overall_satisfaction;
						$ratings[] = array(
							"name"=>"satisfaction", 
							"score"=>$rs->overall_satisfaction
						);
						$ratings[] = array(
							"name"=>"performance", 
							"score"=>$rs->performance_rating
						);
						$ratings[] = array(
							"name"=>"recommendation", 
							"score"=>$rs->recommendation_rating
						);
					}
					$data["ratings"] = $ratings;		

					//create the items array
					$items = array();
					foreach ($rating_data->channel->item as $item){
						$rs_item = $item->children('http://rss.realsatisfied.com/ns/realsatisfied/');	
						//add to array...
						if($data["feed_format"]=='detailed'){
							$items[] = array(
								"name"=>$item->title, 
								"content"=>$item->description,
								"customer_type"=>$rs_item->customer_type,
								"date"=>$item->pubDate,
								"id"=>$item->guid,
								"satisfation"=>$rs_item->satisfaction,
								"recomendation"=>$rs_item->recommendation,
								"performance"=>$rs_item->performance
							);				
						}else{
							$items[] = array(
								"name"=>$item->title, 
								"content"=>$item->description,
								"customer_type"=>$rs_item->customer_type,
								"date"=>$item->pubDate,
								"id"=>$item->guid,
								"ratings"=>null
							);				
						}

					}
					$data["items"] = $items;

				break;	
			}

			$data["version"] = $this->version;

			/* 
			 * $data Array contains all data related to this feed.
			 * $ratings Array contains name/val pairs for feed specific ratings 
			 * $items Array contains the transaction specific information including ratings where available
			 * use the names from the ratings array to reference the ratings in the $items array
			 * all feeds contain a $data["summaryrating"] where ratings are available.
			 * review the RealSatisfied XML name space for detail on specific data available : http://rss.realsatisfied.com/ns/realsatisfied/
			 */

			return array("status"=>1, "message"=>"OK","data"=>$data);

		}catch (Exception $e){
				return array("status"=>0, "message"=>"Failed","data"=>$e);
		}
	}  
}
?>

		
