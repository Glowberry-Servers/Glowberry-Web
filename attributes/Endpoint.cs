using System;

namespace glowberry.attributes
{
    /// <summary>
    /// This attribute is used to mark a method as an endpoint for the web server.
    /// </summary>
    public sealed class Endpoint : Attribute
    {
        /// <summary>
        /// The endpoint name to be used in the url. (prefix/api/{endpoint}/)
        /// </summary>
        public string Name { get; }
        
        public Endpoint(string path) => Name = path;
    }
}