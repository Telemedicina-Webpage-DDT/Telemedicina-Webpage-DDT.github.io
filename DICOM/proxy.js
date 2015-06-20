var http = require('http'),
    httpProxy = require('http-proxy');

var proxy =  httpProxy.createProxyServer({target:'http://localhost:8042'}).listen(8000);

proxy.on('proxyRes', function(proxyReq, req, res, options) {
  // add the CORS header to the response
  res.setHeader('Access-Control-Allow-Origin', '*');
});

proxy.on('error', function(e) {
  // suppress errors
});