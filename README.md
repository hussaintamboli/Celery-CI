Celery-CI
=========

celery.php (celery-php) transformed into a CodeIgniter's library. Original celery.php taken from https://github.com/gjedeer/celery-php/blob/master/celery.php  

Demo
----
Make a request (celery client) : http://localhost/celerycontroller/addTwoNumbers

Assuming you are running a python module as a celery worker it will add numbers 2 and 3.

tasks.py
--------

    from celery import Celery

    celery = Celery('tasks', broker='amqp://guest:guest@localhost:5672//')

    @celery.task
    def add(x, y):
        return x + y

Result
------
    [2013-12-27 15:13:03,530: WARNING/MainProcess] celery@magic-desktop ready.
    [2013-12-27 15:13:09,311: INFO/MainProcess] Received task: tasks.add[php_52bd4bad4b9ad7.61080575]
    [2013-12-27 15:13:09,313: INFO/MainProcess] Task tasks.add[php_52bd4bad4b9ad7.61080575] succeeded in 0.000939602963626s: 5



