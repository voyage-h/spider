<?php namespace system;
abstract class Plugin extends Dispatcher {
    abstract protected function routerStartup();
    abstract protected function routerShutdown();
}