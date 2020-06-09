<?php

Class Db{
    public function connect(){
        return new mysqli("localhost", "root", "", "boysclub");
    }
}

/*<?php

Class Db{
    public function connect(){
        return new mysqli("sql200.byetcluster.com", "epiz_25968220", "VzquesCnyy2o9~z^", "epiz_25968220_boysclub");
    }
}



Class Db{
    public function connect(){
        return new mysqli("localhost", "id13787317_root", "VzquesCnyy2o9~z^", "id13787317_boysclub");
    }
}
*/