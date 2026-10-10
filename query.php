foreach(DB::table("landing_pages")->where("extras", "!=", "[]")->whereNotNull("extras")->get() as $r) { echo $r->id . ": " . $r->extras . PHP_EOL; }
