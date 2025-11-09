import {createApi, fetchBaseQuery} from '@reduxjs/toolkit/query/react';

export const pokemonService = createApi({
  reducerPath: 'pokemonService',
  baseQuery: fetchBaseQuery({baseUrl: 'https://pokeapi.co/api/v2/'}),
  endpoints: builder => ({
    getPokemon: builder.query({
      query: name => `pokemon/${name}`,
    }),
    // Add other endpoints here if needed
  }),
});

export const {useGetPokemonQuery} = pokemonService;
