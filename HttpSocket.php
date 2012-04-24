<?php
class HTTPSocket extends BufferedTcpSocket {
    private $rawheader = '';
    protected $headers;
    protected $body;
    public function init() {
        parent::init();
        $this->setLine();
    }
    public function onReadLine($data) {
        if ($data == "\r\n") {
            $this->headerDone($this->rawheader);
        } else {
            $this->rawheader .= $data;
        }
    }
     public function parseHeaders($str) {
         $header = array();
         $lines = preg_split('/[\r\n]/', $str, -1, PREG_SPLIT_NO_EMPTY);
         $initial_line = array_shift($lines);

         $header['status'] = $initial_line;

         foreach ($lines as $l) {
             $l = explode(':',$l,2);
             if (strtolower($l[0]) == 'content-length') {
                 $this->res_content_length = (int)$l[1];
             }
             $header[urldecode(trim($l[0]))] = urldecode(trim($l[1]));
         }
         return $header;
     }

    public function headerDone($raw_header) {
        //parse header
        $this->headers = $this->parseHeaders($raw_header);
        $this->emit('HeaderReceived', $this, $this->headers);
        //read length
        $content_length = @$this->headers['Content-Length'];
        if($content_length == null) $content_length = 0;
        //set chunk size and get data
        if($content_length > 0) {
            $this->setChunkSize($content_length);
        } else {
            $this->requestDone();
        }
    }
    public function onReadChunk($data) {
        $this->body = $data;
        $this->requestDone();
    }
    protected function requestDone() {
        echo "httpdone\n";
        var_dump($this->callbacks);
        $this->emit('RequestDone', $this->headers, $this->body);
    }
}
