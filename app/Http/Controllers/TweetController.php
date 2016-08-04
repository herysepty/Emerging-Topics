<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;

// $k = adalah total mention  pada Training pada user @herysepty
// j adalah total mention  yang ada pada user @herysepty pada rentang waktu pukul 10:00:00
// α=β=0,5 

class TweetController extends Controller
{
	private $total_mention_per_tweet = 0;
	private $total_mentions = 0;
    
    public function index()
    {
    	$tweets = DB::table('tweets')->get();

		echo "Jumlah tweets : ".count($tweets)."<br/>";
		foreach ($tweets as $key => $value) {
			echo $value->id_str." => ";
			echo $value->screen_name." => ";
			echo $this->countMention($value->text)."=>";
			echo $value->text."<br/>";
			$mentions = array();
		}
		

		echo "<br/>==================== Total keseluruhan mention ========================<br/>";
		echo "jumlah mentions : ".$this->total_mentions;
		echo "<br/>============== Hitung probabilitas mention per tweet===================<br/>";
		$probabilitas = 1;
		$total_mentions = $this->total_mentions;
		foreach ($tweets as $key => $value) {
			$total_mention_per_tweet = $this->countMention($value->text);
			$prob = $this->probabilitasMention($total_mention_per_tweet,$total_mentions)."<br/>";
			echo $prob;
			$probabilitas = $probabilitas*$prob;
		}
		echo "<br/>========================== probabilitas mention=========================<br/>";
		echo "total probabilitas mention : ".$probabilitas;
    }

	public function countMention($text)
	{
		$word = explode(" ",$text);
		$this->total_mention_per_tweet = 0;
		foreach ($word as  $value) {
			if(substr($value, 0,1) == '@')
			{
				$this->total_mention_per_tweet++;
			}
		}
		$this->total_mentions = $this->total_mentions + $this->total_mention_per_tweet;
		return $this->total_mention_per_tweet;
	}

	public function probabilitasMention($total_mention_per_tweet,$total_mentions)
	{
		return $total_probabilitas = ($total_mentions + 0.5)/($total_mention_per_tweet + $total_mentions + 0.5);
	}

	public function LinkAnomaly($k,$m,$n)
	{
		$p = ($m+0.5)/($m+$k+0.5);
		return $p;
	}

	public function download()
    {
        $consumer_key        = "YyiX1I2pgTKaMAI4UbKxkFCJ0";
        $consumer_secret     = "Kxw9IrUjFHz5IcVFhiBPUmjr1FxAvwSt3zveo2oPKqro1PMUni";
        $access_token        = "715720484491399169-BRLVVh1oqYsy7Hq3bf1pRkWTfqCIHLc";
        $access_token_secret = "66q4RVaIq8NPHcc01mEaXXJXqPmLvqUfdtcbjuUvnBTHx";
        $twitter             = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
        $twitter->setTimeouts(10, 360000);
        $keywords = array('pilkadadki2017');
        foreach ($keywords as $value_keyword)
        {

            $tweets = $twitter->get("search/tweets", ["q" => $value_keyword,"count"=>100,"result_type"=>"recent"]);
            if(!empty($tweets->statuses))
            {
                foreach ($tweets->statuses as $tweet)
                {
                    $check_tweet = DB::table('tweets')->where('id_str' , $tweet->id_str)->count();
                    if($check_tweet == 0)
                    {
                        DB::table('tweets')
                        			->insert([
					                          	'id_str' => $tweet->id_str,
					                           	'screen_name' => $tweet->user->screen_name,
					                           	'text' => $tweet->text,
					                           	'created_at' => $tweet->created_at
				                           	]);
                    }
                }
            }
            else
            {
            	echo "no tweet";
            }
        }
    }

}
