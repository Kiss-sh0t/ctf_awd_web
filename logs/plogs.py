import time
from logging import getLogger

#settings/base.py MIDDLEWARE_CLASSES+=django.middleware.plogs.LoggingMiddleware
class LoggingMiddleware(object):
    def __init__(self):
        # arguably poor taste to use django's logger
        self.logger = getLogger('django.request')

    def process_request(self, request):
        #request.timer = time()
        t = time.strftime("%H:%M:%S", time.localtime())

        ip = request.META['REMOTE_ADDR'] or ''
        ua = request.META['HTTP_USER_AGENT'] or ''
        cookie = request.META['HTTP_COOKIE'] or ''
        method = request.META['REQUEST_METHOD'] or ''
        body = request.body or ''
        if (request.META['QUERY_STRING']==''):
            url = request.META['PATH_INFO']
        else:
            url = request.META['PATH_INFO']+'?'+request.META['QUERY_STRING'] or ''
        log = '[%s]--[IP:%s]--Method:%s--url:%s--POST:%s--UA:%s--cookie:%s\n'%(t,ip, method,url,body,ua,cookie)
        with open('/path_to_log', 'a+') as f:
            f.write(log)
        return None

    def process_response(self, request, response):
        return response
