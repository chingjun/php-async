<?php
class BufferedTcpSocket extends TcpSocket {
    const LINEMODE = 1;
    const CHUNKMODE = 2;
    private $buffer;
    private $delimiter = "\r\n";
    public function init() {
        parent::init();
    }
    public function processData() {
        if ($this->mode == self::LINEMODE) {
            $pos = strpos($this->buffer, $this->delimiter);
            if ($pos !== false) {
                $len = $pos + strlen($this->delimiter);
                $retdata = substr($this->buffer, 0, $len);
                $this->buffer = substr($this->buffer, $len);
                $this->emit('ReadLine', $retdata);
            } else {
                return false;
            }
        } else {
            if (strlen($this->buffer) >= $this->chunksize) {
                $retdata = substr($this->buffer, 0, $this->chunksize);
                $this->buffer = substr($this->buffer, $this->chunksize);
                $this->emit('ReadChunk', $retdata);
            } else {
                return false;
            }
        }
    }
    public function onRead($data) {
        $this->buffer .= $data;
        while ($this->processData() !== false);
    }
    public function setMode($mode) {
        if($mode == 'line' || $mode == self::LINEMODE) {
            $this->mode = self::LINEMODE;
        } else {
            $this->mode = self::CHUNKMODE;
        }
    }
    public function setChunkSize($size) {
        $this->setMode(self::CHUNKMODE);
        $this->chunksize = $size;
    }
    public function setLine($delimiter = "\r\n") {
        $this->setMode(self::LINEMODE);
        $this->delimiter = $delimiter;
    }
}
